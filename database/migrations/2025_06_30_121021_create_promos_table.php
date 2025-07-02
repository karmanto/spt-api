<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title_id');
            $table->string('title_en');
            $table->string('title_ru');
            $table->text('description_id');
            $table->text('description_en');
            $table->text('description_ru');
            $table->string('price');
            $table->string('old_price')->nullable();
            $table->string('image');
            $table->dateTime('end_date');
            $table->string('pdf_url');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promos');
    }
};