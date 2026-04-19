<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['composition_id', 'supplier_id', 'qty', 'date', 'note'])]
class StockIn extends Model
{
    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
