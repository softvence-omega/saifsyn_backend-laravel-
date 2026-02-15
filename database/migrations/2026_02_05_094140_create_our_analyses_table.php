<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('our_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');                      // Stock symbol (AAPL, TSLA...)
            $table->string('name');                        // Stock name
            $table->string('status');                      // COMPLIANT / NON_COMPLIANT / QUESTIONABLE
            $table->decimal('debt_to_market_cap_ratio', 8, 4)->nullable();
            $table->float('securities_to_market_cap_ratio')->nullable();
            $table->float('compliant_revenue')->nullable();
            $table->float('non_compliant_revenue')->nullable();
            $table->float('questionable_revenue')->nullable();
            $table->text('recommendation')->nullable();   // Admin for own analysis / recommendation
            $table->text('note')->nullable();             // Admin for note user notification for user
            $table->softDeletes(); // <- this is required for SoftDeletes
            $table->timestamps();

            $table->unique(['symbol', 'created_at']);      // Same stock same date duplicate 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('our_analyses');
    }
};
