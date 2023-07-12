<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model{
    protected $table = 'aula';
    protected $fillable = ['nama', 'kapasitas', 'lokasi', 'deskripsi', 'status'];
}