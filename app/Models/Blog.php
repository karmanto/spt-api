<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_id',
        'title_en',
        'title_ru',
        'content_id',
        'content_en',
        'content_ru',
        'image',
        'posting_date',
    ];

    protected $casts = [
        'posting_date' => 'datetime',
    ];
}