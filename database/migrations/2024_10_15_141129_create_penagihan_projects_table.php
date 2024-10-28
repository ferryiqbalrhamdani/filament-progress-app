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
        Schema::create('penagihan_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penagihan_id')->nullable()->constrained('penagihans')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('status')->default(false);
            $table->string('jenis_penagihan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penagihan_projects');
    }
};
