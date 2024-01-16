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
        Schema::create('penyewaan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk');
            $table->string('type_user');
            $table->string('kegiatan');
            $table->date('tanggal_pengajuan');
            $table->string('penanggung_jawab');
            $table->string('asal_surat');
            $table->string('jenis_surat');
            $table->enum('type', ['mobil', 'aula']);
            $table->enum('status', ['Menunggu Persetujuan', 'Disetujui BAU', 'Ditolak BAU', 'Disetujui Kepala Bagian Umum', 'Ditolak Kepala Bagian Umum', 'Selesai'])->default('Menunggu Persetujuan');
            $table->date('tanggal_persetujuan_bau')->nullable();
            $table->date('tanggal_persetujuan_kepala_bagian_umum')->nullable();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->text('disposisi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaan');
    }
};
