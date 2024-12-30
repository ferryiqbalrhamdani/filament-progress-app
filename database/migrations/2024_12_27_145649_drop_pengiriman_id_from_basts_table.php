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
        Schema::table('basts', function (Blueprint $table) {
            $table->dropForeign(['pengiriman_id']); // Menghapus foreign key constraint
            $table->dropColumn('pengiriman_id');   // Menghapus kolom
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengiriman')->onDelete('cascade');
        });
    }
};
