<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['composition_code', 'name', 'unit', 'current_stock', 'minimum_stock'])]
class Composition extends Model
{
    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function restockPredictions(): HasMany
    {
        return $this->hasMany(RestockPrediction::class);
    }
}
