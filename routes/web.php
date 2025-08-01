<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\NotificationController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('customers', AdminCustomerController::class);
    Route::get('/customers/{customer}/wallet-pass', [AdminCustomerController::class, 'generateWalletPass'])->name('customers.wallet-pass');
    Route::get('/customers/{customer}/wallet-qr', [AdminCustomerController::class, 'showWalletQR'])->name('customers.wallet-qr');
    Route::get('/customers/{customer}/wallet-preview', [AdminCustomerController::class, 'previewWalletDesign'])->name('customers.wallet-preview');
    Route::post('/customers/{customer}/wallet-design', [AdminCustomerController::class, 'saveWalletDesign'])->name('customers.wallet-design');
    Route::post('/wallet-design/global', [AdminCustomerController::class, 'saveGlobalWalletDesign'])->name('wallet-design.global');
    Route::post('/customers/{customer}/force-update-wallet', [AdminCustomerController::class, 'forceUpdateWallet'])->name('customers.force-update-wallet');
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/customers/{customer}/notifications', [NotificationController::class, 'customerNotifications'])->name('notifications.customer');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
