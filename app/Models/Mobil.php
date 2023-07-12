<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model{
    protected $table = 'mobil';
    protected $fillable = ['merk', 'plat_nomer', 'kapasitas', 'warna', 'status'];
}