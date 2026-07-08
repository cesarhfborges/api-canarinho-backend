<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SystemSeeder::class);

        if (env('RUN_LOAD_TEST_SEEDER', false)) {
            $this->call(LoadTestSeeder::class);
        } elseif (app()->environment() !== 'production') {
            $this->call(DevSeeder::class);
        }
    }
}
