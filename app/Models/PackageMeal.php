<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageMeal extends Model
{
    protected $casts = [
        'description' => 'json',
    ];

    protected $fillable = [
        'itinerary_id', 
        'description'
    ];

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(PackageItinerary::class);
    }
}
