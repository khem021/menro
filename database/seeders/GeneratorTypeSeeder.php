<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneratorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('generator_types')->count() === 0) {
            DB::table('generator_types')->insert([
                [
                    'type_name'   => 'Residential',
                    'description' => 'Households and dwelling units generating domestic waste.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'type_name'   => 'Commercial',
                    'description' => 'Businesses, shops, markets, and commercial establishments.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'type_name'   => 'Industrial',
                    'description' => 'Manufacturing plants, factories, and industrial facilities.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'type_name'   => 'Institutional',
                    'description' => 'Schools, hospitals, government offices, and other institutions.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'type_name'   => 'Agricultural',
                    'description' => 'Farms, fishponds, and other agricultural operations.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }
}
