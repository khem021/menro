<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WasteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('waste_categories')->count() === 0) {
            DB::table('waste_categories')->insert([
                [
                    'category_name' => 'Biodegradable',
                    'description'   => 'Organic waste that can be decomposed naturally, such as food scraps and yard waste.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'category_name' => 'Recyclable',
                    'description'   => 'Materials that can be reprocessed and reused, such as paper, plastic, glass, and metal.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'category_name' => 'Residual/Non-Recyclable',
                    'description'   => 'Waste that cannot be composted or recycled and must go to a sanitary landfill.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'category_name' => 'Special/Hazardous',
                    'description'   => 'Waste that poses a significant risk to health or the environment, such as chemicals, batteries, and medical waste.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'category_name' => 'Electronic Waste',
                    'description'   => 'Discarded electrical or electronic devices such as computers, phones, and appliances.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'category_name' => 'Construction Debris',
                    'description'   => 'Waste generated from construction, renovation, or demolition activities.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
            ]);
        }
    }
}
