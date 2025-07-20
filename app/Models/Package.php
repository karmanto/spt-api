<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Package extends Model
{
    protected $casts = [
        'name' => 'json',
        'duration' => 'json',
        'location' => 'json',
        'overview' => 'json',
    ];

    protected $fillable = [
        'code', 
        'tour_type',
        'name',
        'duration',
        'location',
        'original_price', 
        'starting_price',
        'rate', 
        'overview',
        'tags',
        'order',
    ];

    public function highlights(): HasMany
    {
        return $this->hasMany(PackageHighlight::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(PackageItinerary::class);
    }

    public function includedExcluded(): HasMany
    {
        return $this->hasMany(PackageIncludedExcluded::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(PackageFaq::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function cancellationPolicies(): HasMany
    {
        return $this->hasMany(PackageCancellationPolicy::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PackagePrice::class);
    }
}