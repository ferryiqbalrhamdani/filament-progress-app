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
        Schema::create('marcendiser_ready_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marcendiser_id')->constrained()->onDelete('cascade');
            $table->foreignId('ready_stock_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcendiser_ready_stock');
    }
};
