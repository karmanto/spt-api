<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageIncludedExcluded extends Model
{
    protected $fillable = [
        'package_id', 
        'type',
        'description'
    ];

    protected $casts = [
        'description' => 'json',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}