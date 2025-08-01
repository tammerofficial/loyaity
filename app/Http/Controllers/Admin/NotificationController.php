<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Display notifications for a specific customer.
     */
    public function customerNotifications(Customer $customer)
    {
        $notifications = DB::table('notifications')
            ->where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.notifications.customer', compact('notifications', 'customer'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();
        
        if ($notification) {
            DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $customerId = $request->input('customer_id');
        
        if ($customerId) {
            DB::table('notifications')
                ->where('notifiable_type', Customer::class)
                ->where('notifiable_id', $customerId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            DB::table('notifications')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete notification.
     */
    public function destroy($id)
    {
        DB::table('notifications')->where('id', $id)->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count for AJAX.
     */
    public function unreadCount()
    {
        $count = DB::table('notifications')
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
