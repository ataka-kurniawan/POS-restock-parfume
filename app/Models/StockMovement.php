<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['composition_id', 'type', 'reference_type', 'reference_id', 'qty', 'stock_before', 'stock_after', 'movement_date', 'note'])]
class StockMovement extends Model
{
    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }

    /**
     * Get the reference model (Sale, StockIn, etc.)
     */
    public function reference()
    {
        return $this->morphTo(null, 'reference_type', 'reference_id');
    }
}
