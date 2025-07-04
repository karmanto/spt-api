<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFaq extends Model
{
    protected $casts = [
        'question' => 'json',
        'answer' => 'json',
    ];

    protected $fillable = ['package_id', 'question', 'answer'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}