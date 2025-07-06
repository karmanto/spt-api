<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('package_cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->json('description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('package_cancellation_policies');
    }
};