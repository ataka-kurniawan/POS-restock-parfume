<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin & Owner Routes
    Route::middleware(['role:admin,owner'])->group(function () {
        Route::resource('categories', App\Http\Controllers\CategoryController::class);
        Route::resource('products', App\Http\Controllers\ProductController::class);
        Route::resource('compositions', App\Http\Controllers\CompositionController::class);
        Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
        Route::resource('products.recipes', App\Http\Controllers\ProductRecipeController::class);
        
        Route::get('/stock-ins', [App\Http\Controllers\StockInController::class, 'index'])->name('stock-ins.index');
        Route::get('/stock-ins/create', [App\Http\Controllers\StockInController::class, 'create'])->name('stock-ins.create');
        Route::post('/stock-ins', [App\Http\Controllers\StockInController::class, 'store'])->name('stock-ins.store');
        
        Route::get('/stock-movements', [App\Http\Controllers\StockMovementController::class, 'index'])->name('stock-movements.index');
        
        Route::get('/reports/sales', [App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
    });

    // Cashier Routes
    Route::middleware(['role:kasir,admin'])->group(function () {
        Route::get('/pos', [App\Http\Controllers\SaleController::class, 'create'])->name('pos.index');
        Route::post('/pos', [App\Http\Controllers\SaleController::class, 'store'])->name('pos.store');
        Route::get('/sales', [App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');
    });

    // Owner specific routes
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/restock-predictions', [App\Http\Controllers\RestockPredictionController::class, 'index'])->name('restock-predictions.index');
        Route::post('/restock-predictions/generate', [App\Http\Controllers\RestockPredictionController::class, 'generatePredictions'])->name('restock-predictions.generate');
    });

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
});

require __DIR__.'/auth.php';
