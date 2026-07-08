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
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropUnique(['project_id', 'name']);
            $table->foreignId('parent_id')->nullable()->after('project_id')->constrained('endpoints')->onDelete('cascade');
            $table->unique(['project_id', 'parent_id', 'name']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'parent_id', 'name']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->unique(['project_id', 'name']);
        });
    }
};
