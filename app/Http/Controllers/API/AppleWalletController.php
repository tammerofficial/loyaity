<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\AppleWalletPass;
use App\Services\AppleWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppleWalletController extends Controller
{
    protected $appleWalletService;

    public function __construct(AppleWalletService $appleWalletService)
    {
        $this->appleWalletService = $appleWalletService;
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

        // Update pass data with latest customer info
        $customer = $pass->customer;
        $passData = [
            'formatVersion' => 1,
            'passTypeIdentifier' => $pass->pass_type_id,
            'teamIdentifier' => env('APPLE_WALLET_TEAM_ID'),
            'serialNumber' => $pass->serial_number,
            'authenticationToken' => $pass->authentication_token,
            'webServiceURL' => url('/api/apple-wallet'),
            'organizationName' => config('app.name'),
            'description' => 'Loyalty Card',
            'logoText' => config('app.name'),
            'foregroundColor' => 'rgb(255, 255, 255)',
            'backgroundColor' => 'rgb(0, 122, 255)',
            'labelColor' => 'rgb(255, 255, 255)',
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
    public function registerDevice($passTypeId, $serialNumber, Request $request)
    {
        // Implementation for device registration would go here
        // This requires Apple Push Notification service setup
        
        return response()->json(['message' => 'Device registered'], 201);
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
    public function unregisterDevice($passTypeId, $serialNumber, $deviceId, Request $request)
    {
        // Implementation for device unregistration would go here
        
        return response('', 200);
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
