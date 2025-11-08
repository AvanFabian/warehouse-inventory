<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'throttle:web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:sensitive');

    // Master data
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::post('products/import', [App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::get('products-export', [App\Http\Controllers\ProductController::class, 'export'])->name('products.export');

    // Warehouses
    Route::resource('warehouses', App\Http\Controllers\WarehouseController::class);

    // Transactions
    Route::resource('stock-ins', App\Http\Controllers\StockInController::class)->except(['edit', 'update']);
    Route::resource('stock-outs', App\Http\Controllers\StockOutController::class)->except(['edit', 'update']);
    Route::get('products/{productId}/stock', [App\Http\Controllers\StockOutController::class, 'getProductStock'])->name('products.stock');

    // Inter-Warehouse Transfers
    Route::resource('transfers', App\Http\Controllers\InterWarehouseTransferController::class)->except(['edit', 'update']);
    Route::post('transfers/{transfer}/approve', [App\Http\Controllers\InterWarehouseTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('transfers/{transfer}/reject', [App\Http\Controllers\InterWarehouseTransferController::class, 'reject'])->name('transfers.reject');
    Route::post('transfers/{transfer}/start-transit', [App\Http\Controllers\InterWarehouseTransferController::class, 'startTransit'])->name('transfers.start-transit');
    Route::post('transfers/{transfer}/complete', [App\Http\Controllers\InterWarehouseTransferController::class, 'complete'])->name('transfers.complete');

    // Stock Opname
    Route::resource('stock-opnames', App\Http\Controllers\StockOpnameController::class)->except(['edit', 'update', 'show']);

    // Reports
    Route::get('reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/transactions', [App\Http\Controllers\ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('reports/inventory-value', [App\Http\Controllers\ReportController::class, 'inventoryValue'])->name('reports.inventory-value');
    Route::get('reports/stock-card', [App\Http\Controllers\ReportController::class, 'stockCard'])->name('reports.stock-card');

    // User Management (Admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);

        // Settings (Admin only)
        Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    });
});

require __DIR__ . '/auth.php';

// Fallback route for 404 errors
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
