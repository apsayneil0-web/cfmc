<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Crop;

class CropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $crops = [
            ['name' => 'Rice', 'description' => 'Rice crops'],
            ['name' => 'Corn', 'description' => 'Corn crops'],
            ['name' => 'Cassava', 'description' => 'Cassava crops'],
        ];

        foreach ($crops as $crop) {
            Crop::create($crop);
        }
    }
}