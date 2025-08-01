<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Customer;
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
            // Create a simple store card
            $pass = new StoreCard(
                organizationName: 'Tammer Loyalty System',
                description: 'Loyalty Card',
                passTypeIdentifier: env('APPLE_WALLET_PASS_TYPE_ID'),
                teamIdentifier: env('APPLE_WALLET_TEAM_ID'),
                serialNumber: $customer->membership_number
            );

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

            // Manually create the factory with p12 certificate file
            $passFactory = new PassFactory([
                'certificate' => storage_path('certs/tammer.wallet.p12'),
                'wwdr' => storage_path('certs/AppleWWDRCAG3.pem'),
                'password' => ''
            ]);
            
            // Skip signature for testing (remove in production)
            $passFactory->setSkipSignature(true);

            // Generate the pass
            $passFile = $passFactory->create($pass);
            $passContent = file_get_contents($passFile->getRealPath());

            // Clean up temp file
            unlink($passFile->getRealPath());

            // Return the pass as download
            return new Response($passContent, 200, [
                'Content-Type' => 'application/vnd.apple.pkpass',
                'Content-Disposition' => 'attachment; filename="loyalty_card_' . $customer->membership_number . '.pkpass"',
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
     * Show QR Code for downloading Apple Wallet pass.
     */
    public function showWalletQR(Customer $customer)
    {
        $walletPassUrl = route('admin.customers.wallet-pass', $customer);
        
        return view('admin.customers.wallet-qr', [
            'customer' => $customer,
            'walletPassUrl' => $walletPassUrl
        ]);
    }
}
