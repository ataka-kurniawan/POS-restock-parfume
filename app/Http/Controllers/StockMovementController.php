<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with('composition')->latest()->get();
        return view('stock_movements.index', compact('movements'));
    }
}
