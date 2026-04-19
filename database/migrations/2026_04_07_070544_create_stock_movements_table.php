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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composition_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->string('reference_type')->nullable(); // Sale, StockIn, ManualAdjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('qty', 15, 2);
            $table->decimal('stock_before', 15, 2);
            $table->decimal('stock_after', 15, 2);
            $table->dateTime('movement_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
