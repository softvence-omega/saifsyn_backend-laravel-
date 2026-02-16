<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zakat_calculation_id')->constrained('zakat_calculations')->onDelete('cascade');
            $table->string('symbol');
            $table->enum('strategy', ['ACTIVE', 'PASSIVE']);
            $table->string('currency', 5);
            $table->float('quantity', 15, 4);
            $table->float('unit_price', 15, 4);
            $table->float('market_value', 15, 4);
            $table->float('zakat_liable_amount', 15, 4);
            $table->float('zakat_due', 15, 4);
            $table->enum('calculation_method', ['TREAT_AS_CASH','PASSIVE_INVESTMENT','FALLBACK_30']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_holdings');
    }
};
