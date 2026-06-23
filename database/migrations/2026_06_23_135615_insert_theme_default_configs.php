<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('configs')->insert([
            [
                'key' => 'theme_preset',
                'value' => json_encode('Aura'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'theme_primary',
                'value' => json_encode('emerald'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'theme_surface',
                'value' => json_encode(null),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'theme_menuMode',
                'value' => json_encode('static'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configs')->whereIn('key', [
            'theme_preset',
            'theme_primary',
            'theme_surface',
            'theme_menuMode',
        ])->delete();
    }
};
