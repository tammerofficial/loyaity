<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\AppleWalletPass;
use App\Models\WalletDeviceRegistration;
use App\Models\WalletDesignSettings;
use App\Services\AppleWalletService;
use App\Services\AppleWalletPushService;
use App\Services\AppleWalletUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppleWalletController extends Controller
{
    protected $appleWalletService;
    protected $pushService;
    protected $updateService;

    public function __construct(
        AppleWalletService $appleWalletService, 
        AppleWalletPushService $pushService,
        AppleWalletUpdateService $updateService
    ) {
        $this->appleWalletService = $appleWalletService;
        $this->pushService = $pushService;
        $this->updateService = $updateService;
    }

    /**
     * Generate and download Apple Wallet pass for customer
     */
    public function generatePass(Customer $customer)
    {
        try {
            $pkpassPath = $this->appleWalletService->generatePass($customer);
            
            return response()->download($pkpassPath, $customer->name . '_loyalty_card.pkpass')
                ->deleteFileAfterSend();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate pass: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Force update pass with new design and data
     */
    public function forceUpdatePass($serialNumber, Request $request)
    {
        try {
            $pass = AppleWalletPass::where('serial_number', $serialNumber)
                ->where('is_active', true)
                ->first();

            if (!$pass) {
                return response()->json(['error' => 'Pass not found'], 404);
            }

            $customer = $pass->customer;
            
            // Use the update service to force update the pass
            $result = $this->updateService->forceUpdateCustomerPass($customer);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Force update pass failed', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get pass data for Apple Wallet web service
     */
    public function getPass($passTypeId, $serialNumber, Request $request)
    {
        $authToken = $request->header('Authorization');
        
        $pass = AppleWalletPass::where('serial_number', $serialNumber)
            ->where('authentication_token', str_replace('ApplePass ', '', $authToken))
            ->where('is_active', true)
            ->first();

        if (!$pass) {
            return response()->json(['error' => 'Pass not found'], 404);
        }

        // Update pass data with latest customer info and design settings
        $customer = $pass->customer;
        $designSettings = WalletDesignSettings::getCustomerSettings($customer->id);
        
        $passData = [
            'formatVersion' => 1,
            'passTypeIdentifier' => $pass->pass_type_id,
            'teamIdentifier' => env('APPLE_WALLET_TEAM_ID'),
            'serialNumber' => $pass->serial_number,
            'authenticationToken' => $pass->authentication_token,
            'webServiceURL' => url('/api/apple-wallet'),
            'organizationName' => $designSettings->organization_name,
            'description' => 'Loyalty Card',
            'logoText' => $designSettings->organization_name,
            'foregroundColor' => $this->hexToRgb($designSettings->text_color),
            'backgroundColor' => $this->hexToRgb($designSettings->background_color),
            'labelColor' => $this->hexToRgb($designSettings->label_color),
            'storeCard' => [
                'headerFields' => [
                    [
                        'key' => 'tier',
                        'label' => 'Tier',
                        'value' => strtoupper($customer->tier)
                    ]
                ],
                'primaryFields' => [
                    [
                        'key' => 'name',
                        'label' => 'Member',
                        'value' => $customer->name
                    ]
                ],
                'secondaryFields' => [
                    [
                        'key' => 'points',
                        'label' => 'Available Points',
                        'value' => number_format($customer->available_points)
                    ],
                    [
                        'key' => 'member_since',
                        'label' => 'Member Since',
                        'value' => $customer->joined_at ? $customer->joined_at->format('M Y') : 'N/A'
                    ]
                ]
            ]
        ];

        $pass->pass_data = $passData;
        $pass->last_updated = now();
        $pass->save();

        return response()->json($passData);
    }

    /**
     * Register device for push notifications
     */
    public function registerDevice($deviceLibraryIdentifier, $passTypeId, $serialNumber, Request $request)
    {
        try {
            // Get authorization token from request
            $authToken = $request->header('Authorization');
            
            if (!$authToken) {
                return response()->json(['error' => 'Authorization required'], 401);
            }

            // Find the pass
            $pass = AppleWalletPass::where('serial_number', $serialNumber)
                ->where('pass_type_id', $passTypeId)
                ->where('authentication_token', str_replace('ApplePass ', '', $authToken))
                ->where('is_active', true)
                ->first();

            if (!$pass) {
                return response()->json(['error' => 'Pass not found'], 404);
            }

            // Get push token from request body
            $pushToken = $request->input('pushToken');

            // Register or update device
            $registration = WalletDeviceRegistration::updateOrCreate([
                'device_library_identifier' => $deviceLibraryIdentifier,
                'pass_type_identifier' => $passTypeId,
                'serial_number' => $serialNumber,
            ], [
                'apple_wallet_pass_id' => $pass->id,
                'push_token' => $pushToken,
                'registered_at' => now(),
                'is_active' => true,
            ]);

            Log::info('Device registered for pass updates', [
                'device' => $deviceLibraryIdentifier,
                'pass' => $serialNumber,
                'customer' => $pass->customer_id
            ]);

            return response()->json(['message' => 'Device registered successfully'], 201);

        } catch (\Exception $e) {
            Log::error('Device registration failed', [
                'error' => $e->getMessage(),
                'device' => $deviceLibraryIdentifier,
                'pass' => $serialNumber
            ]);

            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * Get serial numbers for passes that need updates
     */
    public function getUpdates($passTypeId, Request $request)
    {
        $passesUpdatedSince = $request->query('passesUpdatedSince');
        
        $query = AppleWalletPass::where('pass_type_id', $passTypeId)
            ->where('is_active', true);

        if ($passesUpdatedSince) {
            $query->where('last_updated', '>', date('c', $passesUpdatedSince));
        }

        $serialNumbers = $query->pluck('serial_number')->toArray();

        if (empty($serialNumbers)) {
            return response('', 204);
        }

        return response()->json([
            'serialNumbers' => $serialNumbers,
            'lastUpdated' => time()
        ]);
    }

    /**
     * Unregister device
     */
    public function unregisterDevice($deviceLibraryIdentifier, $passTypeId, $serialNumber, Request $request)
    {
        try {
            // Get authorization token from request
            $authToken = $request->header('Authorization');
            
            if (!$authToken) {
                return response()->json(['error' => 'Authorization required'], 401);
            }

            // Find and deactivate the registration
            $registration = WalletDeviceRegistration::where('device_library_identifier', $deviceLibraryIdentifier)
                ->where('pass_type_identifier', $passTypeId)
                ->where('serial_number', $serialNumber)
                ->first();

            if ($registration) {
                $registration->update(['is_active' => false]);
                
                Log::info('Device unregistered from pass updates', [
                    'device' => $deviceLibraryIdentifier,
                    'pass' => $serialNumber
                ]);
            }

            return response('', 200);

        } catch (\Exception $e) {
            Log::error('Device unregistration failed', [
                'error' => $e->getMessage(),
                'device' => $deviceLibraryIdentifier,
                'pass' => $serialNumber
            ]);

            return response('', 500);
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
     * Log Apple Wallet requests
     */
    public function logRequest(Request $request)
    {
        Log::info('Apple Wallet Log Request', [
            'logs' => $request->input('logs', [])
        ]);

        return response('', 200);
    }

    /**
     * Show pass preview/info page
     */
    public function showPass(Customer $customer)
    {
        $pass = AppleWalletPass::where('customer_id', $customer->id)
            ->where('is_active', true)
            ->first();

        if (!$pass) {
            return response()->json(['error' => 'No active pass found for this customer'], 404);
        }

        return response()->json([
            'customer' => [
                'name' => $customer->name,
                'tier' => $customer->tier,
                'membership_number' => $customer->membership_number,
                'available_points' => $customer->available_points,
                'total_points' => $customer->total_points,
                'joined_at' => $customer->joined_at
            ],
            'pass' => [
                'serial_number' => $pass->serial_number,
                'download_count' => $pass->download_count,
                'last_updated' => $pass->last_updated,
                'download_url' => route('apple-wallet.generate', $customer->id)
            ]
        ]);
    }
}
