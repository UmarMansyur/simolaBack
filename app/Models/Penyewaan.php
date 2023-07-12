<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model{
    protected $table = 'penyewaan';
    protected $fillable = ['tanggal_pengajuan', 'penanggung_jawab', 'asal_surat', 'jenis_surat', 'type', 'tanggal_mulai', 'tanggal_selesai', 'lampiran'];
}