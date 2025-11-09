<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Inventory KPIs
        $totalProducts = Product::count();
        $lowStockCount = Product::whereColumn('stock', '<', 'min_stock')->count();

        $transactionsThisMonth = StockIn::whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->count() +
            StockOut::whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->count();

        $lowStockProducts = Product::whereColumn('stock', '<', 'min_stock')
            ->with('category')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Sales KPIs
        $totalCustomers = Customer::where('is_active', true)->count();

        // Sales this month
        $salesThisMonth = SalesOrder::whereYear('order_date', date('Y'))
            ->whereMonth('order_date', date('m'))
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->sum('total');

        // Pending orders (confirmed but not delivered)
        $pendingOrdersCount = SalesOrder::whereIn('status', ['confirmed', 'shipped'])->count();

        // Invoices overview
        $totalUnpaidInvoices = Invoice::where('payment_status', 'unpaid')->sum('total_amount');
        $overdueInvoicesCount = Invoice::where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', now())
            ->count();

        // Recent sales orders
        $recentOrders = SalesOrder::with(['customer', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent invoices
        $recentInvoices = Invoice::with(['salesOrder.customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'transactionsThisMonth',
            'lowStockProducts',
            'totalCustomers',
            'salesThisMonth',
            'pendingOrdersCount',
            'totalUnpaidInvoices',
            'overdueInvoicesCount',
            'recentOrders',
            'recentInvoices'
        ));
    }
}
