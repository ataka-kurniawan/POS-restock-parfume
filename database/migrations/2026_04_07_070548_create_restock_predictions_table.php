<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restock_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composition_id')->constrained()->onDelete('cascade');
            $table->string('period'); // e.g., '2026-04'
            $table->string('predicted_label'); // e.g., 'High Priority', 'Medium Priority', 'Low Priority'
            $table->decimal('probability', 5, 2)->nullable();
            $table->decimal('recommendation_score', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_predictions');
    }
};
