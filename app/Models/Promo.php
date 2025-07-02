<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_id',
        'title_en',
        'title_ru',
        'description_id',
        'description_en',
        'description_ru',
        'price',
        'old_price',
        'image',
        'end_date',
        'pdf_url',
    ];

    protected $casts = [
        'end_date' => 'datetime',
    ];
}