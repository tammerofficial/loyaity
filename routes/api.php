<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\LoyaltyCardController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\AppleWalletController;

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

// Protected routes that require authentication
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Admin routes can be added here
});
