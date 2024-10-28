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
        Schema::create('pengiriman_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengiriman')->cascadeOnDelete();
            $table->string('jenis_pengiriman')->nullable();
            $table->date('tanggal_pengiriman')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_projects');
    }
};
