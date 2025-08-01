<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\LoyaltyCard;
use App\Models\AppleWalletPass;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_cards' => LoyaltyCard::where('status', 'active')->count(),
            'total_points_earned' => Transaction::where('type', 'earned')->sum('points'),
            'total_points_redeemed' => abs(Transaction::where('type', 'redeemed')->sum('points')),
            'apple_wallet_downloads' => AppleWalletPass::sum('download_count'),
        ];

        $recentCustomers = Customer::with('loyaltyCards')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentTransactions = Transaction::with(['customer', 'loyaltyCard'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $tierDistribution = Customer::selectRaw('tier, COUNT(*) as count')
            ->groupBy('tier')
            ->get()
            ->pluck('count', 'tier');

                // Monthly earnings trend (last 6 months)
        $monthlyEarnings = Transaction::where('type', 'earned')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->selectRaw('strftime("%Y-%m", created_at) as month, SUM(points) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentCustomers',
            'recentTransactions',
            'tierDistribution',
            'monthlyEarnings'
        ));
    }
}
