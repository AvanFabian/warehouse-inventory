<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('dashboard.index', compact('totalProducts', 'lowStockCount', 'transactionsThisMonth', 'lowStockProducts'));
    }
}
