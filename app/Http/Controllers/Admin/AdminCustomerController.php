<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Customer;
use App\Models\WalletDesignSettings;
use App\Services\AppleWalletPushService;
use Chiiya\Passes\Apple\Components\Barcode;
use Chiiya\Passes\Apple\Components\Field;
use Chiiya\Passes\Apple\Components\SecondaryField;
use Chiiya\Passes\Apple\Components\Image;
use Chiiya\Passes\Apple\Enumerators\BarcodeFormat;
use Chiiya\Passes\Apple\Enumerators\ImageType;
use Chiiya\Passes\Apple\Passes\StoreCard;
use Chiiya\Passes\Apple\PassFactory;

class AdminCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'points_to_add' => 'nullable|integer|min:0',
            'points_to_redeem' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        if ($request->filled('points_to_add')) {
            $customer->earnPoints($request->points_to_add, $request->description);
        }

        if ($request->filled('points_to_redeem')) {
            try {
                $customer->redeemPoints($request->points_to_redeem, $request->description);
            } catch (\Exception $e) {
                return back()->withErrors(['msg' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

        /**
     * Generate Apple Wallet pass for the customer.
     */
    public function generateWalletPass(Customer $customer)
    {
        try {
            // Get design settings for this customer
            $designSettings = WalletDesignSettings::getCustomerSettings($customer->id);

            // Create a simple store card
            $pass = new StoreCard(
                organizationName: $designSettings->organization_name,
                description: 'Loyalty Card',
                passTypeIdentifier: config('app.apple_wallet_pass_type_id', 'pass.com.tammer.loyaltycard'),
                teamIdentifier: config('app.apple_wallet_team_id', '6SGU7C9M42'),
                serialNumber: $customer->membership_number
            );

            // Enable web service URL for automatic updates (points, balance, etc.)
            $pass->webServiceURL = 'https://192.168.8.143/api/v1/apple-wallet'; // Use HTTPS for production
            $pass->authenticationToken = 'auth_' . $customer->membership_number . '_' . hash('sha256', time() . $customer->id);


            // Apply design settings - convert hex to rgb format
            $pass->backgroundColor = $this->hexToRgb($designSettings->background_color);
            $pass->foregroundColor = $this->hexToRgb($designSettings->text_color);
            $pass->labelColor = $this->hexToRgb($designSettings->label_color);

            // Add customer-specific fields
            $pass->primaryFields = [
                new Field(
                    key: 'balance',
                    value: number_format($customer->available_points),
                    label: 'Available Points'
                )
            ];

            $pass->secondaryFields = [
                new SecondaryField(
                    key: 'tier',
                    value: $customer->tier,
                    label: 'Tier'
                ),
                new SecondaryField(
                    key: 'member',
                    value: $customer->membership_number,
                    label: 'Member #'
                )
            ];

            // Add QR Code barcode for easy scanning
            $pass->barcodes = [
                new Barcode(
                    format: BarcodeFormat::QR,
                    message: json_encode([
                        'customer_id' => $customer->id,
                        'membership_number' => $customer->membership_number,
                        'points' => $customer->available_points,
                        'tier' => $customer->tier,
                        'generated_at' => now()->toISOString()
                    ]),
                    messageEncoding: 'iso-8859-1',
                    altText: 'Member: ' . $customer->membership_number
                )
            ];

            // Add required icon
            $pass->addImage(new Image(
                storage_path('wallet-icons/icon.png'),
                ImageType::ICON
            ));

            // Try to use P12 with environment variable workaround for LibreSSL
            try {
                // Set environment variables to force legacy crypto on systems like macOS
                putenv('OPENSSL_CONF=/dev/null');
                
                // Create PassFactory with P12 file from config
                $passFactory = new PassFactory([
                    'certificate' => config('app.apple_wallet_certificate_path'),
                    'wwdr' => config('app.apple_wallet_wwdr_certificate_path'),
                    'password' => config('app.apple_wallet_certificate_password'),
                ]);
                
                \Log::info('Using P12 certificate file with LibreSSL compatibility mode.', [
                    'cert_path' => config('app.apple_wallet_certificate_path'),
                    'wwdr_path' => config('app.apple_wallet_wwdr_certificate_path'),
                ]);
                
            } catch (\Exception $e) {
                // If P12 still fails, create unsigned pass
                $passFactory = new PassFactory();
                $passFactory->setSkipSignature(true);
                
                \Log::warning('Using unsigned pass due to certificate issues', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }

            // Generate the pass
            $passFile = $passFactory->create($pass);
            $passContent = file_get_contents($passFile->getRealPath());

            // Save pass to local storage for uploading to server
            $localPassesDir = storage_path('app/wallet-passes');
            if (!is_dir($localPassesDir)) {
                mkdir($localPassesDir, 0755, true);
            }
            
            $passFileName = 'loyalty_card_' . $customer->membership_number . '.pkpass';
            $localPassPath = $localPassesDir . '/' . $passFileName;
            file_put_contents($localPassPath, $passContent);

            // Upload to remote server
            $this->uploadPassToServer($localPassPath, $passFileName);

            // Clean up temp files
            unlink($passFile->getRealPath());

            // Return the pass as download
            return new Response($passContent, 200, [
                'Content-Type' => 'application/vnd.apple.pkpass',
                'Content-Disposition' => 'attachment; filename="' . $passFileName . '"',
                'Content-Length' => strlen($passContent),
            ]);

        } catch (\Exception $e) {
            // Log the detailed error
            \Log::error('Wallet pass generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error generating wallet pass: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Upload pass file to remote server.
     */
    private function uploadPassToServer($localPath, $fileName)
    {
        try {
            $serverHost = '192.168.8.143';
            $serverUser = 'alalawi310';
            $serverPassword = 'aaaaaaaa';
            $serverPath = '/var/www/html/applecards/';
            
            // Use sshpass for automated password authentication
            $scpCommand = "sshpass -p '$serverPassword' scp -o StrictHostKeyChecking=no '$localPath' $serverUser@$serverHost:$serverPath$fileName";
            
            // Try to upload file
            $output = [];
            $returnCode = 0;
            exec($scpCommand, $output, $returnCode);
            
            if ($returnCode === 0) {
                \Log::info('Wallet pass uploaded to server successfully', [
                    'file' => $fileName,
                    'server' => $serverHost,
                    'path' => $serverPath
                ]);
            } else {
                \Log::warning('Upload may have failed', [
                    'return_code' => $returnCode,
                    'output' => implode('\n', $output),
                    'file' => $fileName
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to upload pass to server', [
                'error' => $e->getMessage(),
                'file' => $fileName
            ]);
        }
    }

    /**
     * Show QR Code for downloading Apple Wallet pass.
     */
    public function showWalletQR(Customer $customer)
    {
        // Use remote server URL with PHP script for proper headers + timestamp to avoid caching
        $walletPassUrl = 'http://192.168.8.143/download_pass.php?file=applecards/loyalty_card_' . $customer->membership_number . '.pkpass&t=' . time();
        
        return view('admin.customers.wallet-qr', [
            'customer' => $customer,
            'walletPassUrl' => $walletPassUrl,
            'timestamp' => time()
        ]);
    }

    /**
     * Preview Apple Wallet pass design.
     */
    public function previewWalletDesign(Customer $customer)
    {
        $designSettings = WalletDesignSettings::getCustomerSettings($customer->id);
        $globalSettings = WalletDesignSettings::getGlobalSettings();
        
        return view('admin.customers.wallet-preview', [
            'customer' => $customer,
            'designSettings' => $designSettings,
            'globalSettings' => $globalSettings
        ]);
    }

    /**
     * Save wallet design settings for a specific customer.
     */
    public function saveWalletDesign(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color_secondary' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'label_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_image_url' => 'nullable|url',
            'background_opacity' => 'nullable|integer|min:0|max:100',
            'use_background_image' => 'nullable|boolean',
            'apply_to' => 'required|in:customer,global'
        ]);

        $pushService = new AppleWalletPushService();
        $notificationsSent = 0;

        if ($validated['apply_to'] === 'global') {
            WalletDesignSettings::saveGlobalSettings($validated);
            
            // Send push notifications to all registered devices
            $notificationsSent = $pushService->notifyGlobalDesignUpdate();
            
            $message = 'تم حفظ إعدادات التصميم العامة وتم إرسال ' . $notificationsSent . ' إشعار للأجهزة المسجلة.';
        } else {
            WalletDesignSettings::saveCustomerSettings($customer->id, $validated);
            
            // Send push notifications to this customer's registered devices
            $notificationsSent = $pushService->notifyCustomerPassUpdates($customer->id);
            
            $message = 'تم حفظ إعدادات التصميم لهذا العميل وتم إرسال ' . $notificationsSent . ' إشعار.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'notifications_sent' => $notificationsSent
        ]);
    }

    /**
     * Save global wallet design settings.
     */
    public function saveGlobalWalletDesign(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color_secondary' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'label_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        WalletDesignSettings::saveGlobalSettings($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الإعدادات العامة. ستؤثر على جميع العملاء.'
        ]);
    }

    /**
     * Convert hex color to rgb format required by Apple Wallet.
     */
    private function hexToRgb($hex)
    {
        // Remove # if present
        $hex = ltrim($hex, '#');
        
        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgb($r, $g, $b)";
    }
}
