<?php
/**
 * Loyalty Wallet Bridge - Central Bridge File
 * 
 * هذا الملف يعمل كوسيط مركزي بين الداشبورد المحلي وبطاقات Apple Wallet
 * موجود على السيرفر: http://192.168.8.143/
 * 
 * الإعدادات:
 * 1. تعديل $DASHBOARD_URL ليشير للداشبورد المحلي
 * 2. تعديل $BRIDGE_SECRET لمفتاح أمان فريد
 * 3. استخدام هذا الرابط في البطاقات كـ webServiceURL
 * 
 * الاستخدام في البطاقات:
 * "webServiceURL": "http://192.168.8.143/applecards/loyalty_wallet_bridge.php"
 */

// إعدادات CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Bridge-Secret');

// التعامل مع طلبات OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== CONFIGURATION =====
$DASHBOARD_URL = 'http://192.168.8.151:8000'; // رابط الداشبورد المحلي (جهازك)
$BRIDGE_SECRET = 'loyalty-bridge-secret-2024'; // مفتاح أمان فريد
$LOG_FILE = 'loyalty_bridge.log'; // ملف السجلات
$BRIDGE_NAME = 'Loyalty Wallet Bridge v2.0'; // اسم الجسر

// ===== UTILITY FUNCTIONS =====
function logMessage($message, $data = []) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    if (!empty($data)) {
        $logEntry .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    $logEntry .= "\n";
    file_put_contents($LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

function sendToDashboard($endpoint, $data = [], $method = 'GET') {
    global $DASHBOARD_URL, $BRIDGE_SECRET;
    
    $url = $DASHBOARD_URL . $endpoint;
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Bridge-Secret: ' . $BRIDGE_SECRET,
        'User-Agent: ' . $BRIDGE_NAME
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    logMessage("Dashboard request: $method $url", [
        'sent_data' => $data,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ]);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

function respondJson($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestPath() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/applecards/loyalty_wallet_bridge.php', '', $path);
    return trim($path, '/');
}

function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

function getRequestHeaders() {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header = str_replace('HTTP_', '', $key);
            $header = str_replace('_', '-', $header);
            $headers[$header] = $value;
        }
    }
    return $headers;
}

function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

// ===== MAIN ROUTING LOGIC =====
$path = getRequestPath();
$method = getRequestMethod();
$headers = getRequestHeaders();
$body = getRequestBody();

logMessage("Request received", [
    'method' => $method,
    'path' => $path,
    'headers' => $headers,
    'body' => $body,
    'bridge_name' => $BRIDGE_NAME,
    'x_bridge_secret_provided' => isset($headers['X-Bridge-Secret']),
    'x_bridge_secret_value' => $headers['X-Bridge-Secret'] ?? 'not_provided',
    'bridge_secret_expected' => $BRIDGE_SECRET
]);

// التحقق من مفتاح الأمان للطلبات الإدارية
// سيتم التحقق من المفتاح في كل مسار إداري على حدة

// ===== ROUTE HANDLING =====

// المسار الأساسي - معلومات الجسر
if ($path === '' && $method === 'GET') {
    respondJson([
        'bridge_name' => $BRIDGE_NAME,
        'bridge_version' => '2.0.0',
        'status' => 'active',
        'message' => 'Loyalty Wallet Bridge is running',
        'dashboard_url' => $DASHBOARD_URL,
        'server_ip' => '192.168.8.143',
        'available_endpoints' => [
            'GET /status' => 'Bridge status',
            'GET /passes/{passTypeId}/{serialNumber}' => 'Get pass data',
            'POST /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}' => 'Register device',
            'GET /devices/{deviceId}/registrations/{passTypeId}' => 'Get updates',
            'DELETE /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}' => 'Unregister device',
            'POST /log' => 'Apple Wallet logs'
        ]
    ]);
}

// حالة النظام
if ($path === 'status' && $method === 'GET') {
    $dashboardStatus = sendToDashboard('/api/wallet-bridge/status');
    
    $status = [
        'bridge_status' => 'active',
        'bridge_name' => $BRIDGE_NAME,
        'bridge_version' => '2.0.0',
        'dashboard_connection' => $dashboardStatus['success'] ? 'connected' : 'disconnected',
        'timestamp' => date('c'),
        'log_file' => $LOG_FILE,
        'dashboard_url' => $DASHBOARD_URL,
        'server_ip' => '192.168.8.143'
    ];
    
    if (!$dashboardStatus['success']) {
        $status['error'] = $dashboardStatus['error'];
    }
    
    respondJson($status);
}

// Apple Wallet Web Service Routes
if (preg_match('/^passes\/([^\/]+)\/([^\/]+)$/', $path, $matches) && $method === 'GET') {
    // GET /passes/{passTypeId}/{serialNumber}
    $passTypeId = $matches[1];
    $serialNumber = $matches[2];
    $authToken = $headers['Authorization'] ?? null;
    
    logMessage("Pass data request", [
        'pass_type_id' => $passTypeId,
        'serial_number' => $serialNumber,
        'auth_token' => $authToken ? 'provided' : 'not_provided'
    ]);
    
    $result = sendToDashboard("/api/wallet-bridge/passes/$passTypeId/$serialNumber", [], 'GET');
    
    if ($result['success']) {
        respondJson($result['data']);
    } else {
        respondJson(['error' => 'Pass not found'], 404);
    }
}

if (preg_match('/^devices\/([^\/]+)\/registrations\/([^\/]+)\/([^\/]+)$/', $path, $matches)) {
    $deviceLibraryIdentifier = $matches[1];
    $passTypeId = $matches[2];
    $serialNumber = $matches[3];
    
    if ($method === 'POST') {
        // POST /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}
        logMessage("Device registration request", [
            'device_id' => $deviceLibraryIdentifier,
            'pass_type_id' => $passTypeId,
            'serial_number' => $serialNumber
        ]);
        
        $result = sendToDashboard("/api/wallet-bridge/devices/$deviceLibraryIdentifier/registrations/$passTypeId/$serialNumber", $body, 'POST');
        
        if ($result['success']) {
            respondJson($result['data'], 201);
        } else {
            respondJson(['error' => 'Registration failed'], 500);
        }
    } elseif ($method === 'DELETE') {
        // DELETE /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}
        logMessage("Device unregistration request", [
            'device_id' => $deviceLibraryIdentifier,
            'pass_type_id' => $passTypeId,
            'serial_number' => $serialNumber
        ]);
        
        $result = sendToDashboard("/api/wallet-bridge/devices/$deviceLibraryIdentifier/registrations/$passTypeId/$serialNumber", [], 'DELETE');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Unregistration failed'], 500);
        }
    }
}

if (preg_match('/^devices\/([^\/]+)\/registrations\/([^\/]+)$/', $path, $matches) && $method === 'GET') {
    // GET /devices/{deviceId}/registrations/{passTypeId}
    $deviceLibraryIdentifier = $matches[1];
    $passTypeId = $matches[2];
    $lastUpdated = $_GET['passesUpdatedSince'] ?? null;
    
    logMessage("Device updates request", [
        'device_id' => $deviceLibraryIdentifier,
        'pass_type_id' => $passTypeId,
        'last_updated' => $lastUpdated
    ]);
    
    $result = sendToDashboard("/api/wallet-bridge/devices/$deviceLibraryIdentifier/registrations/$passTypeId", ['passesUpdatedSince' => $lastUpdated], 'GET');
    
    if ($result['success']) {
        respondJson($result['data']);
    } else {
        respondJson(['error' => 'Failed to get updates'], 500);
    }
}

if ($path === 'log' && $method === 'POST') {
    // POST /log
    logMessage("Apple Wallet log request", $body);
    
    $result = sendToDashboard('/api/wallet-bridge/log', $body, 'POST');
    
    if ($result['success']) {
        respondJson($result['data']);
    } else {
        respondJson(['error' => 'Logging failed'], 500);
    }
}

// إدارة النظام Routes (تتطلب مفتاح الأمان)
logMessage("Checking admin routes", [
    'x_bridge_secret_provided' => isset($headers['X-Bridge-Secret']),
    'x_bridge_secret_value' => $headers['X-Bridge-Secret'] ?? 'not_provided',
    'bridge_secret_expected' => $BRIDGE_SECRET,
    'secret_matches' => isset($headers['X-Bridge-Secret']) && $headers['X-Bridge-Secret'] === $BRIDGE_SECRET
]);

if (isset($headers['X-Bridge-Secret']) && $headers['X-Bridge-Secret'] === $BRIDGE_SECRET) {
    
    if ($path === 'logs' && $method === 'GET') {
        // GET /logs
        $lines = $_GET['lines'] ?? 100;
        $result = sendToDashboard("/api/wallet-bridge/logs?lines=$lines");
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Failed to get logs'], 500);
        }
    }
    
    if ($path === 'logs' && $method === 'DELETE') {
        // DELETE /logs
        logMessage("Clear logs request");
        
        $result = sendToDashboard('/api/wallet-bridge/logs', [], 'DELETE');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Failed to clear logs'], 500);
        }
    }
    
    if ($path === 'test-connection' && $method === 'GET') {
        // GET /test-connection
        logMessage("Test connection request");
        
        $result = sendToDashboard('/api/wallet-bridge/test-dashboard-connection');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Connection test failed'], 500);
        }
    }
    
    if ($path === 'statistics' && $method === 'GET') {
        // GET /statistics
        logMessage("Statistics request");
        
        $result = sendToDashboard('/api/wallet-bridge/statistics');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Failed to get statistics'], 500);
        }
    }
    
    if ($path === 'restart' && $method === 'POST') {
        // POST /restart
        logMessage("Restart service request");
        
        $result = sendToDashboard('/api/wallet-bridge/restart-service', [], 'POST');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Failed to restart service'], 500);
        }
    }
    
    if ($path === 'push-notification' && $method === 'POST') {
        // POST /push-notification
        logMessage("Push notification request", $body);
        
        $result = sendToDashboard('/api/wallet-bridge/push-notification', $body, 'POST');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Push notification failed'], 500);
        }
    }
    
    if ($path === 'update-pass' && $method === 'POST') {
        // POST /update-pass
        logMessage("Update pass request", $body);
        
        $result = sendToDashboard('/api/wallet-bridge/update-pass', $body, 'POST');
        
        if ($result['success']) {
            respondJson($result['data']);
        } else {
            respondJson(['error' => 'Pass update failed'], 500);
        }
    }
}

// إذا لم يتم العثور على المسار
logMessage("Route not found", ['path' => $path, 'method' => $method]);
respondJson(['error' => 'Route not found'], 404); 