<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\WalletManagementController;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\NotificationController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Customer Routes
    Route::resource('customers', AdminCustomerController::class);
    
    // Customer Wallet Routes
    Route::prefix('customers/{customer}')->name('customers.')->group(function () {
        Route::get('/wallet-preview', [AdminCustomerController::class, 'previewWalletDesign'])->name('wallet-preview');
        Route::post('/wallet-design', [AdminCustomerController::class, 'saveWalletDesign'])->name('save-wallet-design');
        Route::get('/wallet-pass', [AdminCustomerController::class, 'generateWalletPass'])->name('wallet-pass');
        Route::get('/generate-wallet-pass', [AdminCustomerController::class, 'generateWalletPass'])->name('generate-wallet-pass');
        Route::get('/wallet-qr', [AdminCustomerController::class, 'showWalletQR'])->name('wallet-qr');
        Route::post('/force-update-wallet', [AdminCustomerController::class, 'forceUpdateWallet'])->name('force-update-wallet');
    });
    
    // Global Wallet Design Route
    Route::post('/wallet-design/global', [AdminCustomerController::class, 'saveGlobalWalletDesign'])->name('save-global-wallet-design');
    
    // Wallet Management Routes
    Route::prefix('wallet-management')->name('wallet-management.')->group(function () {
        Route::get('/', [WalletManagementController::class, 'index'])->name('index');
        Route::get('/{customer}', [WalletManagementController::class, 'show'])->name('show');
        Route::get('/statistics', [WalletManagementController::class, 'bridgeStatistics'])->name('statistics');
        Route::post('/{customer}/add-points', [WalletManagementController::class, 'addPoints'])->name('add-points');
        Route::post('/{customer}/redeem-points', [WalletManagementController::class, 'redeemPoints'])->name('redeem-points');
        Route::post('/{customer}/update-points', [WalletManagementController::class, 'updatePoints'])->name('update-points');
        Route::post('/{customer}/send-notification', [WalletManagementController::class, 'sendNotification'])->name('send-notification');
    });
    
    // Logo Management Routes
    Route::resource('logos', LogoController::class);
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/customer/{customer}', [NotificationController::class, 'customer'])->name('customer');
    });
});
