<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mock_data', function (Blueprint $table) {
            $table->unsignedBigInteger('mock_id')->default(1)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mock_data', function (Blueprint $table) {
            $table->dropColumn('mock_id');
        });
    }
};
