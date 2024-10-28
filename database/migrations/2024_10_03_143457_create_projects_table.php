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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // step 1
            $table->string('nama_pengadaan')->nullable();
            $table->string('slug')->nullable();
            $table->string('no_up')->nullable();
            $table->foreignId('jenis_lelang_id')->nullable()->constrained('tb_jenis_lelang')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('instansi_id')->nullable()->constrained('instansis')->cascadeOnDelete();
            $table->foreignId('jenis_anggaran_id')->nullable()->constrained('tb_jenis_anggaran')->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->integer('tahun_anggaran')->nullable();
            $table->text('deskripsi')->nullable();

            // step 2
            $table->boolean('bebas_pajak')->default(false);
            $table->string('bebas_pajak_khusus')->nullable();
            $table->string('asal_brand_khusus')->nullable();
            $table->string('payment_term')->nullable();
            $table->boolean('garansi')->default(false);

            // step 3
            $table->string('no_kontrak')->nullable();
            $table->decimal('nilai_kontrak', 15, 2)->default(0);
            $table->date('tanggal_kontrak')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();

            $table->integer('progres')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
