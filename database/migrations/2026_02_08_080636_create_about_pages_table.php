<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->text('description')->nullable(); // About description

            $table->json('our_mission')->nullable(); // JSON for mission
            $table->json('our_vision')->nullable(); // JSON for vision

            $table->string('video')->nullable(); // Video URL or file path

            $table->timestamps();
            $table->softDeletes(); // Soft delete support
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
