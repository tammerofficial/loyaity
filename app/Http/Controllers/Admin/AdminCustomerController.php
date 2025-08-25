<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Customer;
use App\Models\WalletDesignSettings;
use App\Models\Logo;
use App\Services\AppleWalletPushService;
use App\Services\AppleWalletUpdateService;
use App\Notifications\WalletPassCreatedNotification;
use App\Notifications\WalletDesignUpdatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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
        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'initial_points' => 'nullable|integer|min:0',
            'card_number' => 'nullable|string|max:50|unique:loyalty_cards,card_number'
        ]);

        try {
            // Generate unique membership number
            $membershipNumber = $this->generateUniqueMembershipNumber();
            
            // Create customer with initial points
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->birth_date,
                'total_points' => $request->initial_points ?? 0,
                'available_points' => $request->initial_points ?? 0,
                'membership_number' => $membershipNumber,
                'joined_at' => now(),
            ]);

            // Generate card number if not provided
            $cardNumber = $request->card_number;
            if (!$cardNumber) {
                $cardNumber = 'CARD' . str_pad($customer->id, 8, '0', STR_PAD_LEFT);
            }

            // Create loyalty card
            $customer->loyaltyCards()->create([
                'card_number' => $cardNumber,
                'status' => 'active',
                'issued_at' => now(),
            ]);

            // Log the creation
            Log::info('Customer created successfully', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'card_number' => $cardNumber
            ]);

            return redirect()->route('admin.customers.index')
                ->with('success', 'تم إنشاء العميل بنجاح!');

        } catch (\Exception $e) {
            Log::error('Failed to create customer', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'فشل في إنشاء العميل: ' . $e->getMessage()])
                ->withInput();
        }
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'points_to_add' => 'nullable|integer|min:0',
            'points_to_redeem' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            // Update customer information
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
            ]);

            // Handle points adjustments
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

            // Log the update
            Log::info('Customer updated successfully', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'changes' => $request->only(['name', 'email', 'phone', 'date_of_birth'])
            ]);

            return redirect()->route('admin.customers.index')->with('success', 'تم تحديث بيانات العميل بنجاح!');

        } catch (\Exception $e) {
            Log::error('Failed to update customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'فشل في تحديث العميل: ' . $e->getMessage()]);
        }
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
            // Enable web service URL for automatic updates (points, balance, etc.)
            $pass->webServiceURL = config('app.apple_wallet_web_service_url');
            $pass->authenticationToken = 'auth_' . $customer->membership_number . '_' . hash('sha256', time() . $customer->id);


            // Apply design settings - convert hex to rgb format
            $pass->backgroundColor = $this->hexToRgb($designSettings->background_color);
            $pass->foregroundColor = $this->hexToRgb($designSettings->text_color);
            $pass->labelColor = $this->hexToRgb($designSettings->label_color);

            // Add customer-specific fields
            $pass->primaryFields = [
                new Field(
                    key: 'name',
                    value: $customer->name,
                    label: '💎 Member'
                ),
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

            // Get active logo from database
            $activeLogo = Logo::getActiveLogo();
            
            if ($activeLogo) {
                $logoPath = null;
                
                // Check if logo has external URL
                if ($activeLogo->external_url) {
                    try {
                        // Download logo from external URL to temp file with .png extension
                        $response = Http::timeout(30)->get($activeLogo->external_url);
                        if ($response->successful()) {
                            $tempPath = tempnam(sys_get_temp_dir(), 'logo_') . '.png';
                            file_put_contents($tempPath, $response->body());
                            
                            // Verify the downloaded file is a valid PNG image
                            $imageInfo = getimagesize($tempPath);
                            if ($imageInfo && $imageInfo[2] === IMAGETYPE_PNG) {
                                $logoPath = $tempPath;
                                
                                \Log::info('Downloaded logo from external URL', [
                                    'logo_id' => $activeLogo->id,
                                    'logo_name' => $activeLogo->name,
                                    'external_url' => $activeLogo->external_url,
                                    'temp_path' => $tempPath,
                                    'image_size' => $imageInfo[0] . 'x' . $imageInfo[1]
                                ]);
                            } else {
                                // Delete invalid file
                                unlink($tempPath);
                                \Log::warning('Downloaded file is not a valid PNG image', [
                                    'logo_id' => $activeLogo->id,
                                    'external_url' => $activeLogo->external_url
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to download logo from external URL', [
                            'logo_id' => $activeLogo->id,
                            'external_url' => $activeLogo->external_url,
                            'error' => $e->getMessage()
                        ]);
                    }
                } elseif (Storage::exists($activeLogo->file_path)) {
                    // Use the actual logo from storage
                    $logoPath = Storage::path('public/' . $activeLogo->file_path);
                    \Log::info('Using logo from local storage', [
                        'logo_id' => $activeLogo->id,
                        'logo_name' => $activeLogo->name,
                        'logo_path' => $activeLogo->file_path
                    ]);
                }
                
                if ($logoPath) {
                    // Add brand logo
                    $pass->addImage(new Image($logoPath, ImageType::LOGO));
                    
                    // Use the same logo for icon
                    $pass->addImage(new Image($logoPath, ImageType::ICON));
                    
                    \Log::info('Logo successfully added to wallet pass', [
                        'logo_id' => $activeLogo->id,
                        'logo_name' => $activeLogo->name
                    ]);
                } else {
                    // Fallback to default icons
                    $pass->addImage(new Image(
                        storage_path('wallet-icons/icon.png'),
                        ImageType::ICON
                    ));

                    $pass->addImage(new Image(
                        storage_path('wallet-icons/logo.png'),
                        ImageType::LOGO
                    ));
                    
                    \Log::warning('No active logo found or download failed, using default icons for wallet pass');
                }
            } else {
                // Fallback to default icons
                $pass->addImage(new Image(
                    storage_path('wallet-icons/icon.png'),
                    ImageType::ICON
                ));

                $pass->addImage(new Image(
                    storage_path('wallet-icons/logo.png'),
                    ImageType::LOGO
                ));
                
                \Log::warning('No active logo found, using default icons for wallet pass');
            }

            // Try to use P12 with environment variable workaround for LibreSSL
            $certPath = config('app.apple_wallet_certificate_path');
            $wwdrPath = config('app.apple_wallet_wwdr_certificate_path');
            
            try {
                // Set environment variables to force legacy crypto on systems like macOS
                putenv('OPENSSL_CONF=/dev/null');
                
                // Get certificate paths
                $certPath = config('app.apple_wallet_certificate_path');
                $keyPath = config('app.apple_wallet_certificate_key_path');
                $wwdrPath = config('app.apple_wallet_wwdr_certificate_path');
                
                // Check if certificate files exist
                if (!file_exists($certPath)) {
                    throw new \Exception("Certificate file not found: {$certPath}");
                }
                
                if (!file_exists($keyPath)) {
                    throw new \Exception("Certificate key file not found: {$keyPath}");
                }
                
                if (!file_exists($wwdrPath)) {
                    throw new \Exception("WWDR certificate file not found: {$wwdrPath}");
                }
                
                // Create PassFactory with P12 certificate
                $passFactory = new PassFactory([
                    'certificate' => $certPath,
                    'wwdr' => $wwdrPath,
                    'password' => config('app.apple_wallet_certificate_password', ''),
                ]);
                
                Log::info('Using signed pass with PEM certificate', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'cert_path' => $certPath,
                    'wwdr_path' => $wwdrPath
                ]);
                
            } catch (\Exception $e) {
                // If certificate setup fails, fall back to unsigned pass
                Log::warning('Certificate setup failed, using unsigned pass', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                $passFactory = new PassFactory();
                $passFactory->setSkipSignature(true);
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
            
            // Clean up temporary logo file if it was downloaded
            if (isset($logoPath) && $activeLogo && $activeLogo->external_url && file_exists($logoPath)) {
                unlink($logoPath);
                \Log::info('Cleaned up temporary logo file', ['temp_path' => $logoPath]);
            }

            // Send notification to customer about new wallet pass
            try {
                $passUrl = url('/admin/customers/' . $customer->id . '/wallet-qr');
                $customer->notify(new WalletPassCreatedNotification($customer, $passUrl));
                Log::info('Wallet pass creation notification sent to customer', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send wallet pass creation notification', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }

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
        try {
            $validated = $request->validate([
                'organization_name' => 'required|string|max:255',
                'background_color' => 'required|string',
                'background_color_secondary' => 'required|string',
                'text_color' => 'required|string',
                'label_color' => 'required|string',
                'background_image_url' => 'nullable|string',
                'background_opacity' => 'nullable|integer|min:0|max:100',
                'use_background_image' => 'nullable|boolean',
                'apply_to' => 'required|in:customer,global'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة: ' . json_encode($e->errors())
            ], 422);
        }

        \Log::info('Saving wallet design settings', ['validated' => $validated, 'customer_id' => $customer->id]);
        
        try {
            if ($validated['apply_to'] === 'global') {
                \Log::info('Saving global settings');
                WalletDesignSettings::saveGlobalSettings($validated);
                
                // إرسال التحديث للملف المركزي
                $this->sendToWalletBridge('update/global', $validated);
                
                // إعادة إنشاء جميع البطاقات لتطبيق التصميم الجديد
                $this->regenerateAllWalletPasses();
                
                $message = 'تم حفظ إعدادات التصميم العامة وإعادة إنشاء جميع البطاقات بنجاح.';
            } else {
                \Log::info('Saving customer settings for customer: ' . $customer->id);
                WalletDesignSettings::saveCustomerSettings($customer->id, $validated);
                
                // إرسال التحديث للملف المركزي
                $this->sendToWalletBridge("update/design/{$customer->id}", $validated);
                
                // إعادة إنشاء بطاقة العميل المحدد
                $this->regenerateCustomerWalletPass($customer);
                
                $message = 'تم حفظ إعدادات التصميم وإعادة إنشاء بطاقة العميل بنجاح.';
            }
            \Log::info('Settings saved and wallet passes regenerated successfully');
        } catch (\Exception $e) {
            \Log::error('Error saving wallet design settings: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * إرسال البيانات للملف المركزي (Bridge)
     */
    private function sendToWalletBridge($endpoint, $data)
    {
        $bridgeUrl = config('wallet.bridge_url', 'https://your-server.com/wallet_bridge.php');
        $bridgeSecret = config('wallet.bridge_secret', 'your-secret-key-here');
        
        try {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $bridgeUrl . '/' . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Bridge-Secret: ' . $bridgeSecret,
                'User-Agent: LoyaltyDashboard/1.0'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            \Log::info('Bridge request sent', [
                'endpoint' => $endpoint,
                'data' => $data,
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error
            ]);
            
            if ($error) {
                \Log::warning('Bridge connection failed: ' . $error);
                return false;
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                \Log::info('Bridge update successful');
                return true;
            } else {
                \Log::warning('Bridge update failed with HTTP code: ' . $httpCode);
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('Bridge communication error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save global wallet design settings.
     */
    public function saveGlobalWalletDesign(Request $request)
    {
        try {
            $validated = $request->validate([
                'organization_name' => 'required|string|max:255',
                'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_color_secondary' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'label_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ]);

            \Log::info('Saving global wallet design settings', ['validated' => $validated]);

            // حفظ الإعدادات العامة
            WalletDesignSettings::saveGlobalSettings($validated);

            // إعادة إنشاء جميع البطاقات لتطبيق التصميم الجديد
            $regenerationResult = $this->regenerateAllWalletPasses();

            \Log::info('Global design settings saved and wallet passes regenerated', [
                'regeneration_result' => $regenerationResult
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإعدادات العامة وإعادة إنشاء جميع البطاقات بنجاح.',
                'regeneration_stats' => $regenerationResult
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save global wallet design', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email notification to customer about design update.
     */
    private function sendCustomerDesignUpdateNotification(Customer $customer, array $designChanges)
    {
        try {
            $changes = [];
            if (isset($designChanges['background_color'])) {
                $changes[] = 'تم تحديث لون الخلفية';
            }
            if (isset($designChanges['text_color'])) {
                $changes[] = 'تم تحديث لون النص';
            }
            if (isset($designChanges['organization_name'])) {
                $changes[] = 'تم تحديث اسم المنظمة';
            }
            
            $customer->notify(new WalletDesignUpdatedNotification($customer, $changes));
            
            Log::info('Design update notification sent to customer', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'changes' => $changes
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send design update notification to customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notifications to all customers about global design update.
     */
    private function sendGlobalDesignUpdateNotifications(array $designChanges)
    {
        try {
            $changes = [];
            if (isset($designChanges['background_color'])) {
                $changes[] = 'تم تحديث لون الخلفية';
            }
            if (isset($designChanges['text_color'])) {
                $changes[] = 'تم تحديث لون النص';
            }
            if (isset($designChanges['organization_name'])) {
                $changes[] = 'تم تحديث اسم المنظمة';
            }
            
            $customers = Customer::all();
            foreach ($customers as $customer) {
                try {
                    $customer->notify(new WalletDesignUpdatedNotification($customer, $changes));
                } catch (\Exception $e) {
                    Log::warning('Failed to send global design update notification to customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Global design update notifications sent to all customers', [
                'total_customers' => $customers->count(),
                'changes' => $changes
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send global design update notifications', [
                'error' => $e->getMessage()
            ]);
        }
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

    /**
     * Force update wallet pass for customer
     */
    public function forceUpdateWallet(Customer $customer)
    {
        try {
            // Use the update service to force update the pass
            $updateService = app(\App\Services\AppleWalletUpdateService::class);
            $result = $updateService->forceUpdateCustomerPass($customer);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Force update wallet failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إعادة إنشاء بطاقة عميل محدد
     */
    private function regenerateCustomerWalletPass(Customer $customer)
    {
        try {
            \Log::info('Regenerating wallet pass for customer', ['customer_id' => $customer->id]);
            
            // إعادة إنشاء البطاقة
            $this->generateWalletPass($customer);
            
            \Log::info('Wallet pass regenerated successfully', ['customer_id' => $customer->id]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate wallet pass', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * إعادة إنشاء جميع البطاقات
     */
    private function regenerateAllWalletPasses()
    {
        try {
            \Log::info('Starting regeneration of all wallet passes');
            
            $customers = Customer::with('loyaltyCards')->get();
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($customers as $customer) {
                try {
                    $this->regenerateCustomerWalletPass($customer);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Failed to regenerate wallet pass for customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            \Log::info('Wallet passes regeneration completed', [
                'total_customers' => $customers->count(),
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);
            
            return [
                'total' => $customers->count(),
                'success' => $successCount,
                'errors' => $errorCount
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate all wallet passes', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate a unique membership number for new customers
     */
    private function generateUniqueMembershipNumber()
    {
        do {
            // Generate a membership number with format: M + year + 6 digits
            $year = date('Y');
            $randomDigits = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $membershipNumber = 'M' . $year . $randomDigits;
            
            // Check if this membership number already exists
            $exists = Customer::where('membership_number', $membershipNumber)->exists();
        } while ($exists);

        return $membershipNumber;
    }
}
