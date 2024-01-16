<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    protected $table = 'penyewaan';
    protected $fillable = [
        'nomor_induk', 'type_user',
        'kegiatan', 'tanggal_pengajuan', 'penanggung_jawab', 'asal_surat', 'jenis_surat', 'type', 'tanggal_mulai', 'tanggal_selesai', 'lampiran', 'status', 'tanggal_persetujuan_bau', 'tanggal_persetujuan_kepala_bagian_umum', 'disposisi', 'keterangan'
    ];
}
