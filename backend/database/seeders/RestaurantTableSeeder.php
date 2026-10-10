<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RestaurantTable;

class RestaurantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($num = 1; $num<= 25; $num++)
         { 
            RestaurantTable::create([
                'table_number' => $num,
                'capacity' => match (true) {
                    ($num <= 6 || $num == 16 || $num == 17)=>  2,
                    ($num >= 7 && $num <= 12 || $num >= 18 && $num <= 22 ) => 4,
                   default => 6 
                },
                'Status' => 'Available',
                'floor' => ($num < 16) ? 'Ground Floor' : 'First Floor', 
            ]);
         }
    }
}
