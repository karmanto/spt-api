<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageHighlight extends Model
{
    protected $fillable = [
        'package_id', // Tambahkan ini
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