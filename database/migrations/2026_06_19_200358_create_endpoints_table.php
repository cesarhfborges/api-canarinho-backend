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
        Schema::create('endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->boolean('allow_get')->default(true);
            $table->boolean('allow_post')->default(true);
            $table->boolean('allow_put')->default(true);
            $table->boolean('allow_patch')->default(true);
            $table->boolean('allow_delete')->default(true);
            $table->boolean('paginate_response')->default(false);
            $table->unique(['project_id', 'path']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
