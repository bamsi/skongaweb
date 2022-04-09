<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class locationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('locations')->insert([
            ['name' => 'Aruba', 'desirability' => 10],
            ['name' => 'Jamaica', 'desirability' => 10],
            ['name' => 'Tanzania', 'desirability' => 10],
            ['name' => 'Bermuda', 'desirability' => 10],
        ]);
    }
}
