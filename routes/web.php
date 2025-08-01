<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\LogoController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('customers', AdminCustomerController::class);
    Route::get('/customers/{customer}/wallet-pass', [AdminCustomerController::class, 'generateWalletPass'])->name('customers.wallet-pass');
    Route::get('/customers/{customer}/wallet-qr', [AdminCustomerController::class, 'showWalletQR'])->name('customers.wallet-qr');
    Route::get('/customers/{customer}/wallet-preview', [AdminCustomerController::class, 'previewWalletDesign'])->name('customers.wallet-preview');
    Route::post('/customers/{customer}/wallet-design', [AdminCustomerController::class, 'saveWalletDesign'])->name('customers.wallet-design');
    Route::post('/wallet-design/global', [AdminCustomerController::class, 'saveGlobalWalletDesign'])->name('wallet-design.global');
    Route::post('/customers/{customer}/force-update-wallet', [AdminCustomerController::class, 'forceUpdateWallet'])->name('customers.force-update-wallet');
    
    // Logo routes
    Route::resource('logos', LogoController::class);
    Route::post('/logos/{logo}/activate', [LogoController::class, 'activate'])->name('logos.activate');
    Route::post('/logos/{logo}/make-default', [LogoController::class, 'makeDefault'])->name('logos.make-default');
    Route::get('/api/logos/active', [LogoController::class, 'getActiveLogo'])->name('logos.active-api');
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/customers/{customer}/notifications', [NotificationController::class, 'customerNotifications'])->name('notifications.customer');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// إدارة البطاقات عبر الجسر
Route::prefix('admin/wallet-management')->name('admin.wallet-management.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\WalletManagementController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\Admin\WalletManagementController::class, 'show'])->name('show');
    Route::post('/{id}/add-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'addPoints'])->name('add-points');
    Route::post('/{id}/redeem-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'redeemPoints'])->name('redeem-points');
    Route::post('/{id}/update-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'updatePoints'])->name('update-points');
    Route::post('/{id}/send-notification', [App\Http\Controllers\Admin\WalletManagementController::class, 'sendNotification'])->name('send-notification');
    Route::get('/statistics/bridge', [App\Http\Controllers\Admin\WalletManagementController::class, 'bridgeStatistics'])->name('bridge-statistics');
});

// API endpoints لإدارة البطاقات
Route::prefix('api/wallet-management')->name('api.wallet-management.')->group(function () {
    Route::post('/{id}/add-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'apiAddPoints'])->name('add-points');
    Route::post('/{id}/redeem-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'apiRedeemPoints'])->name('redeem-points');
    Route::post('/{id}/update-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'apiUpdatePoints'])->name('update-points');
    Route::post('/{id}/send-notification', [App\Http\Controllers\Admin\WalletManagementController::class, 'apiSendNotification'])->name('send-notification');
});
