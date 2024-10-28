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
        Schema::table('kontraks', function (Blueprint $table) {
            $table->foreignId('user_input_by')->nullable()->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            $table->dropForeign(['user_input_by']);
            $table->dropColumn('user_input_by');
        });
    }
};
