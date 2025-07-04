<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageItinerary extends Model
{
    // Tambahkan properti fillable
    protected $fillable = [
        'package_id', // Wajib ditambahkan
        'day',
        'title'
    ];

    protected $casts = [
        'title' => 'json',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PackageActivity::class, 'itinerary_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(PackageMeal::class, 'itinerary_id');
    }
}