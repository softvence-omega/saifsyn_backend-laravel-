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
        Schema::create('message_notifications', function (Blueprint $table) {
            $table->id();

            // user notification receive  (admin)
            $table->unsignedBigInteger('user_id');

            // which message for notification
            $table->unsignedBigInteger('message_id');

            // read status (0 = unread, 1 = read)
            $table->boolean('is_read')->default(0);

            $table->timestamps();

            // Foreign Keys (optional but recommended)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_notifications');
    }
};
