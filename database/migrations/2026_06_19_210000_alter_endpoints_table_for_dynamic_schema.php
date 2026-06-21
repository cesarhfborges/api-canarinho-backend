<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropUnique(['project_id', 'path']);
            
            $table->dropColumn([
                'path', 
                'allow_get', 
                'allow_post', 
                'allow_put', 
                'allow_patch', 
                'allow_delete', 
                'paginate_response'
            ]);

            $table->string('generator')->nullable();
            $table->json('endpoints_config')->nullable();
            $table->json('resource_schema')->nullable();

            $table->unique(['project_id', 'name']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'name']);
            
            $table->dropColumn(['generator', 'endpoints_config', 'resource_schema']);

            $table->string('path');
            $table->boolean('allow_get')->default(true);
            $table->boolean('allow_post')->default(true);
            $table->boolean('allow_put')->default(true);
            $table->boolean('allow_patch')->default(true);
            $table->boolean('allow_delete')->default(true);
            $table->boolean('paginate_response')->default(false);

            $table->unique(['project_id', 'path']);
        });
    }
};
