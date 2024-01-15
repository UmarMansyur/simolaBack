<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Kepala Bagian Umum',
            'username' => 'kabagum',
            'password' => app('hash')->make('123'),
            'role' => 'Kepala Bagian Umum',
            'thumbnail' => "https://api.unira.ac.id/img/profil/mhs/d9674b9d198eecaa13f3f057d5390a12.jpg"
        ]);

        Admin::create([
            'name' => 'Biro Administrasi Umum',
            'username' => 'bau',
            'password' => app('hash')->make('123'),
            'role' => 'Biro Administrasi Umum',
            'thumbnail' => "https://api.unira.ac.id/img/profil/mhs/d9674b9d198eecaa13f3f057d5390a12.jpg"
        ]);

    }
}
