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
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => app('hash')->make('123'),
            'thumbnail' => "https://api.unira.ac.id/img/profil/mhs/d9674b9d198eecaa13f3f057d5390a12.jpg"

        ]);

    }
}
