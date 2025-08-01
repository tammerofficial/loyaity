<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\LoyaltyCardController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\AppleWalletController;
use App\Http\Controllers\API\WalletBridgeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::prefix('v1')->group(function () {
    // Customer management
    Route::apiResource('customers', CustomerController::class);
    
    // Customer specific actions
    Route::post('customers/{customer}/earn-points', [CustomerController::class, 'earnPoints']);
    Route::post('customers/{customer}/redeem-points', [CustomerController::class, 'redeemPoints']);
    
    // Loyalty cards
    Route::apiResource('loyalty-cards', LoyaltyCardController::class);
    
    // Transactions
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'show']);
    
    // Apple Wallet routes
    Route::prefix('apple-wallet')->group(function () {
        // Generate and download pass
        Route::get('customers/{customer}/pass', [AppleWalletController::class, 'generatePass']);
        
        // Force update pass (for dashboard)
        Route::post('passes/{serialNumber}/force-update', [AppleWalletController::class, 'forceUpdatePass']);
        
        // Apple Wallet web service endpoints
        Route::get('passes/{passTypeId}/{serialNumber}', [AppleWalletController::class, 'getPass']);
        Route::post('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}/{serialNumber}', [AppleWalletController::class, 'registerDevice']);
        Route::get('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}', [AppleWalletController::class, 'getUpdates']);
        Route::delete('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}/{serialNumber}', [AppleWalletController::class, 'unregisterDevice']);
        Route::post('log', [AppleWalletController::class, 'logRequest']);
    });
});

// Wallet Bridge Routes - External Bridge API
Route::prefix('wallet-bridge')->group(function () {
    // System status
    Route::get('status', [WalletBridgeController::class, 'status']);
    
    // Apple Wallet Web Service endpoints (for external bridge)
    Route::get('passes/{passTypeId}/{serialNumber}', [WalletBridgeController::class, 'getPass']);
    Route::post('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}/{serialNumber}', [WalletBridgeController::class, 'registerDevice']);
    Route::get('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}', [WalletBridgeController::class, 'getDeviceUpdates']);
    Route::delete('devices/{deviceLibraryIdentifier}/registrations/{passTypeId}/{serialNumber}', [WalletBridgeController::class, 'unregisterDevice']);
    Route::post('log', [WalletBridgeController::class, 'logRequest']);
    
    // Management endpoints (require bridge secret)
    Route::middleware('bridge.auth')->group(function () {
        Route::post('push-notification', [WalletBridgeController::class, 'sendPushNotification']);
        Route::post('update-pass', [WalletBridgeController::class, 'updatePassData']);
        Route::get('logs', [WalletBridgeController::class, 'getLogs']);
        Route::delete('logs', [WalletBridgeController::class, 'clearLogs']);
        Route::get('test-dashboard-connection', [WalletBridgeController::class, 'testDashboardConnection']);
        Route::get('statistics', [WalletBridgeController::class, 'getStatistics']);
        Route::post('restart-service', [WalletBridgeController::class, 'restartService']);
    });
});

// Protected routes that require authentication
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Admin routes can be added here
});
