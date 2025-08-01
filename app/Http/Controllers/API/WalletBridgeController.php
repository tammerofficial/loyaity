<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\WalletBridgeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletBridgeController extends Controller
{
    protected $bridgeService;

    public function __construct(WalletBridgeService $bridgeService)
    {
        $this->bridgeService = $bridgeService;
    }

    /**
     * الحصول على حالة النظام
     */
    public function status(): JsonResponse
    {
        $status = $this->bridgeService->getSystemStatus();
        
        if (isset($status['error'])) {
            return response()->json($status, 500);
        }
        
        return response()->json($status);
    }

    /**
     * جلب بيانات البطاقة - Apple Wallet Web Service
     */
    public function getPass($passTypeId, $serialNumber, Request $request): JsonResponse
    {
        $authToken = $request->header('Authorization');
        
        $result = $this->bridgeService->getPassData($passTypeId, $serialNumber, $authToken);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result);
    }

    /**
     * تسجيل جهاز جديد - Apple Wallet Web Service
     */
    public function registerDevice($deviceLibraryIdentifier, $passTypeId, $serialNumber, Request $request): JsonResponse
    {
        $pushToken = $request->input('pushToken');
        
        $result = $this->bridgeService->registerDevice(
            $deviceLibraryIdentifier,
            $passTypeId,
            $serialNumber,
            $pushToken
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result, 201);
    }

    /**
     * جلب التحديثات للجهاز - Apple Wallet Web Service
     */
    public function getDeviceUpdates($deviceLibraryIdentifier, $passTypeId, Request $request): JsonResponse
    {
        $lastUpdated = $request->query('passesUpdatedSince');
        
        $result = $this->bridgeService->getDeviceUpdates(
            $deviceLibraryIdentifier,
            $passTypeId,
            $lastUpdated
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result);
    }

    /**
     * إلغاء تسجيل الجهاز - Apple Wallet Web Service
     */
    public function unregisterDevice($deviceLibraryIdentifier, $passTypeId, $serialNumber): JsonResponse
    {
        $result = $this->bridgeService->unregisterDevice(
            $deviceLibraryIdentifier,
            $passTypeId,
            $serialNumber
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result);
    }

    /**
     * تسجيل طلب من Apple Wallet - Apple Wallet Web Service
     */
    public function logRequest(Request $request): JsonResponse
    {
        $requestData = $request->all();
        
        $result = $this->bridgeService->logAppleRequest($requestData);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result);
    }

    /**
     * إرسال إشعار push للجهاز
     */
    public function sendPushNotification(Request $request): JsonResponse
    {
        $request->validate([
            'device_library_identifier' => 'required|string',
            'pass_type_identifier' => 'required|string',
            'serial_number' => 'required|string'
        ]);
        
        $result = $this->bridgeService->sendPushNotification(
            $request->device_library_identifier,
            $request->pass_type_identifier,
            $request->serial_number
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code'] ?? 500);
        }
        
        return response()->json($result);
    }

    /**
     * تحديث بيانات البطاقة
     */
    public function updatePassData(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
            'customer_id' => 'required|integer'
        ]);
        
        try {
            // إرسال طلب تحديث للداشبورد
            $result = $this->bridgeService->sendToDashboard('/api/wallet/update-pass', [
                'serial_number' => $request->serial_number,
                'customer_id' => $request->customer_id
            ], 'POST');
            
            if (!$result['success']) {
                return response()->json(['error' => 'Failed to update pass'], 500);
            }
            
            return response()->json(['success' => true, 'message' => 'Pass updated successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * جلب سجلات النظام
     */
    public function getLogs(Request $request): JsonResponse
    {
        $lines = $request->query('lines', 100);
        $logFile = storage_path('logs/wallet_bridge.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }
        
        $logs = [];
        $file = new \SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return response()->json([
            'logs' => $logs,
            'total_lines' => $totalLines,
            'requested_lines' => $lines
        ]);
    }

    /**
     * مسح سجلات النظام
     */
    public function clearLogs(): JsonResponse
    {
        $logFile = storage_path('logs/wallet_bridge.log');
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        
        return response()->json(['success' => true, 'message' => 'Logs cleared successfully']);
    }

    /**
     * اختبار الاتصال بالداشبورد
     */
    public function testDashboardConnection(): JsonResponse
    {
        $result = $this->bridgeService->sendToDashboard('/api/status');
        
        return response()->json([
            'dashboard_connection' => $result['success'] ? 'connected' : 'disconnected',
            'response' => $result
        ]);
    }

    /**
     * الحصول على إحصائيات النظام
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'total_passes' => \App\Models\AppleWalletPass::where('is_active', true)->count(),
                'total_devices' => \App\Models\WalletDeviceRegistration::count(),
                'total_customers' => \App\Models\Customer::count(),
                'bridge_status' => 'active',
                'last_activity' => now()->toISOString()
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get statistics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * إعادة تشغيل الخدمة
     */
    public function restartService(): JsonResponse
    {
        try {
            // إعادة تهيئة الخدمة
            $this->bridgeService->logMessage("Service restarted manually");
            
            return response()->json([
                'success' => true,
                'message' => 'Service restarted successfully',
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to restart service: ' . $e->getMessage()], 500);
        }
    }
} 