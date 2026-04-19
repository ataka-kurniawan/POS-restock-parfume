<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Composition;
use App\Models\RestockPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        
        $totalSalesToday = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $totalSalesMonth = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');
        $transactionCountToday = Sale::whereDate('sale_date', $today)->count();
        
        $bestSellers = Product::withCount('saleDetails')
            ->orderBy('sale_details_count', 'desc')
            ->take(5)
            ->get();
            
        $lowStockItems = Composition::where('current_stock', '<=', DB::raw('minimum_stock'))
            ->where('current_stock', '>', 0)
            ->get();
            
        $outOfStockItems = Composition::where('current_stock', '<=', 0)->get();
        
        $topRestockPriorities = RestockPrediction::with('composition')
            ->orderBy('recommendation_score', 'desc')
            ->take(5)
            ->get();

        // Data for monthly sales chart
        $monthlySales = Sale::selectRaw('DATE(sale_date) as sale_day, SUM(total_amount) as total')
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->groupBy('sale_day')
            ->orderBy('sale_day')
            ->get();

        $chartLabels = [];
        $chartData = [];
        $daysInMonth = now()->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = now()->day($i)->format('Y-m-d');
            $chartLabels[] = $i; // Day number
            $salesForDay = $monthlySales->where('sale_day', $date)->first();
            $chartData[] = $salesForDay ? (float)$salesForDay->total : 0;
        }

        // Data for restock prediction chart
        $restockPredictions = RestockPrediction::select('predicted_label')
            ->where('period', now()->format('Y-m')) // Filter by current month period
            ->get();

        $predictionCounts = $restockPredictions->countBy('predicted_label');

        $predictionLabels = ['High Priority', 'Medium Priority', 'Low Priority', 'Unknown'];
        $predictionData = [
            $predictionCounts->get('High Priority', 0),
            $predictionCounts->get('Medium Priority', 0),
            $predictionCounts->get('Low Priority', 0),
            $predictionCounts->get('Unknown', 0), // Handle cases not in predictions
        ];

        return view('dashboard', compact(
            'totalSalesToday', 
            'totalSalesMonth',
            'transactionCountToday', 
            'bestSellers', 
            'lowStockItems', 
            'outOfStockItems', 
            'topRestockPriorities',
            'chartLabels',
            'chartData',
            'predictionLabels', // Pass prediction labels for chart
            'predictionData'   // Pass prediction data for chart
        ));
    }
}
