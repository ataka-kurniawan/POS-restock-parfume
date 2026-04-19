<?php

namespace App\Http\Controllers;

use App\Models\RestockPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process; // Import Process for running shell commands

class RestockPredictionController extends Controller
{
    public function index()
    {
        $predictions = RestockPrediction::with('composition')
            ->orderBy('recommendation_score', 'desc')
            ->get();

        return view('restock_predictions.index', compact('predictions'));
    }

    /**
     * Generate new restock predictions by running the Python script.
     */
    public function generatePredictions()
    {
        // Define the path to the Python script
        $pythonScriptPath = base_path('ml/predict_restock.py');
        $pythonExecutable = 'python'; // Or specify the full path if needed, e.g., 'python3' or 'C:\Python310\python.exe'

        // Ensure the script exists before trying to run it
        if (!file_exists($pythonScriptPath)) {
            return back()->withErrors(['error' => 'Python script for predictions not found. Please ensure ml/predict_restock.py exists.']);
        }

        try {
            // Use Laravel's Process facade to run the command
            // Redirecting stderr to stdout to capture all output
            $process = Process::path(base_path())->command("{$pythonExecutable} {$pythonScriptPath} 2>&1")->run();

            $output = $process->output();
            $exitCode = $process->exitCode();

            if ($exitCode !== 0) {
                // Log the error for debugging
                Log::error("Python Prediction Script Failed: Exit Code {$exitCode}", [
                    'output' => $output
                ]);
                return back()->withErrors(['error' => "Gagal menghasilkan prediksi restock. Silakan cek log server untuk detailnya."]);
            }

            // Clear cache to reflect new predictions
            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back()->with('success', 'Prediksi restock berhasil dibuat dan disimpan. Data sudah diperbarui.');

        } catch (\Exception $e) {
            Log::error("Exception during Python Prediction Script execution: " . $e->getMessage());
            return back()->withErrors(['error' => "Terjadi kesalahan saat menjalankan script prediksi: " . $e->getMessage()]);
        }
    }
}
