<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePrice extends Model
{
    protected $fillable = [
        'package_id', 
        'description',
        'price',
        'service_type',
    ];

    protected $casts = [
        'description' => 'json',
        'service_type' => 'json',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}