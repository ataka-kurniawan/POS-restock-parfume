<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Composition;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $sales = Sale::with('user')
            ->whereBetween('sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        
        $bestSellers = Product::withCount(['saleDetails' => function($query) use ($startDate, $endDate) {
                $query->whereHas('sale', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            }])
            ->orderBy('sale_details_count', 'desc')
            ->take(10)
            ->get();

        return view('reports.sales', compact('sales', 'totalRevenue', 'bestSellers', 'startDate', 'endDate'));
    }

    public function stock()
    {
        $compositions = Composition::withCount('stockMovements')->get();
        return view('reports.stock', compact('compositions'));
    }
}
