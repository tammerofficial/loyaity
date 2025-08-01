<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Models\Transaction;
use App\Services\WalletBridgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletManagementController extends Controller
{
    protected $walletBridgeService;

    public function __construct(WalletBridgeService $walletBridgeService)
    {
        $this->walletBridgeService = $walletBridgeService;
    }

    /**
     * عرض صفحة إدارة البطاقات
     */
    public function index()
    {
        $customers = Customer::with(['loyaltyCards', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallet-management.index', compact('customers'));
    }

    /**
     * عرض تفاصيل عميل معين
     */
    public function show($id)
    {
        $customer = Customer::with(['loyaltyCards', 'transactions' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('admin.wallet-management.show', compact('customer'));
    }

    /**
     * إضافة نقاط للعميل
     */
    public function addPoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->addPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        if ($result['success']) {
            return redirect()->back()->with('success', 'تم إضافة النقاط بنجاح!');
        } else {
            return redirect()->back()->with('error', 'فشل في إضافة النقاط: ' . $result['error']);
        }
    }

    /**
     * استبدال نقاط العميل
     */
    public function redeemPoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->redeemPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        if ($result['success']) {
            return redirect()->back()->with('success', 'تم استبدال النقاط بنجاح!');
        } else {
            return redirect()->back()->with('error', 'فشل في استبدال النقاط: ' . $result['error']);
        }
    }

    /**
     * تحديث نقاط العميل يدوياً
     */
    public function updatePoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:0',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->updateCustomerPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        if ($result['success']) {
            return redirect()->back()->with('success', 'تم تحديث النقاط بنجاح!');
        } else {
            return redirect()->back()->with('error', 'فشل في تحديث النقاط: ' . $result['error']);
        }
    }

    /**
     * إرسال إشعار push للعميل
     */
    public function sendNotification(Request $request, $customerId)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $customer = Customer::with('loyaltyCards')->findOrFail($customerId);
        $loyaltyCard = $customer->loyaltyCards->first();

        if (!$loyaltyCard) {
            return redirect()->back()->with('error', 'العميل ليس لديه بطاقة ولاء');
        }

        try {
            $notificationData = [
                'pass_type_id' => 'pass.com.loyalty.customer',
                'serial_number' => $loyaltyCard->id,
                'message' => $request->message,
                'action' => 'custom_notification',
                'customer_id' => $customer->id
            ];

            $response = Http::withHeaders([
                'X-Bridge-Secret' => config('loyalty.bridge_secret'),
                'Content-Type' => 'application/json'
            ])->post(config('loyalty.bridge_url') . '/push-notification', $notificationData);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'تم إرسال الإشعار بنجاح!');
            } else {
                return redirect()->back()->with('error', 'فشل في إرسال الإشعار');
            }

        } catch (\Exception $e) {
            Log::error("Failed to send notification", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'فشل في إرسال الإشعار: ' . $e->getMessage());
        }
    }

    /**
     * عرض إحصائيات الجسر
     */
    public function bridgeStatistics()
    {
        $statistics = $this->walletBridgeService->getBridgeStatistics();
        $logs = $this->walletBridgeService->getBridgeLogs();
        $connectionTest = $this->walletBridgeService->testBridgeConnection();

        return view('admin.wallet-management.statistics', compact('statistics', 'logs', 'connectionTest'));
    }

    /**
     * API endpoint لإضافة نقاط
     */
    public function apiAddPoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->addPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        return response()->json($result);
    }

    /**
     * API endpoint لاستبدال نقاط
     */
    public function apiRedeemPoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->redeemPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        return response()->json($result);
    }

    /**
     * API endpoint لتحديث نقاط
     */
    public function apiUpdatePoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:0',
            'reason' => 'required|string|max:255'
        ]);

        $result = $this->walletBridgeService->updateCustomerPoints(
            $customerId,
            $request->points,
            $request->reason
        );

        return response()->json($result);
    }

    /**
     * API endpoint لإرسال إشعار
     */
    public function apiSendNotification(Request $request, $customerId)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $customer = Customer::with('loyaltyCards')->findOrFail($customerId);
        $loyaltyCard = $customer->loyaltyCards->first();

        if (!$loyaltyCard) {
            return response()->json([
                'success' => false,
                'error' => 'Customer has no loyalty card'
            ]);
        }

        try {
            $notificationData = [
                'pass_type_id' => 'pass.com.loyalty.customer',
                'serial_number' => $loyaltyCard->id,
                'message' => $request->message,
                'action' => 'custom_notification',
                'customer_id' => $customer->id
            ];

            $response = Http::withHeaders([
                'X-Bridge-Secret' => config('loyalty.bridge_secret'),
                'Content-Type' => 'application/json'
            ])->post(config('loyalty.bridge_url') . '/push-notification', $notificationData);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to send notification'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} 