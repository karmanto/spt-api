<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); 
            $table->json('name');
            $table->json('duration');
            $table->json('location');
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('rate', 3, 1)->nullable(); 
            $table->json('overview');
            $table->string('tags');
            $table->smallInteger('order')->nullable(); 
            $table->timestamps();
        });

        Schema::create('package_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->json('description');
            $table->timestamps();
        });

        Schema::create('package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->integer('day');
            $table->json('title');
            $table->timestamps();
        });

        Schema::create('package_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('package_itineraries')->onDelete('cascade');
            $table->string('time');
            $table->json('description');
            $table->timestamps();
        });

        Schema::create('package_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('package_itineraries')->onDelete('cascade');
            $table->json('description');
            $table->timestamps();
        });

        Schema::create('package_included_excludeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['included', 'excluded']);
            $table->json('description');
            $table->timestamps();
        });

        Schema::create('package_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->json('question');
            $table->json('answer');
            $table->timestamps();
        });

        Schema::create('package_cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->json('description');
            $table->timestamps();
        });

        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->json('service_type');
            $table->decimal('price', 10, 2);
            $table->json('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('package_prices');
        Schema::dropIfExists('package_cancellation_policies');
        Schema::dropIfExists('package_faqs');
        Schema::dropIfExists('package_included_excluded');
        Schema::dropIfExists('package_meals');
        Schema::dropIfExists('package_activities');
        Schema::dropIfExists('package_itineraries');
        Schema::dropIfExists('package_highlights');
        Schema::dropIfExists('packages');
    }
};