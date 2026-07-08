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
    public function run(): void
    {
        $this->call(SystemSeeder::class);

        if (config('run_load_test_seeder')) {
            $this->call(LoadTestSeeder::class);
        } elseif (app()->environment() !== 'production') {
            $this->call(DevSeeder::class);
        }
    }
}
