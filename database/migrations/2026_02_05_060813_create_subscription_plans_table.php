<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');                  // Free, Growth, Pro
            $table->string('description');
            $table->decimal('price', 10, 2);          // 0.00, 25.00, 50.00
            $table->json('features');                 // ✅ JSON field for feature list
            $table->string('duration_type')->default('month'); // month, year, day
            $table->integer('duration_value')->default(1);     // 1, 6, 12 etc.
            $table->boolean('is_popular')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
