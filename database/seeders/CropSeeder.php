<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('crops')->insert([
            ['name' => 'Corn', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rice', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cassava', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}