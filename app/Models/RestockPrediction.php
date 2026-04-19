<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['composition_id', 'period', 'predicted_label', 'probability', 'recommendation_score', 'notes'])]
class RestockPrediction extends Model
{
    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }
}
