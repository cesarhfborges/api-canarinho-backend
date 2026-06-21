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
        Schema::create('dynamic_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('endpoints')->onDelete('cascade');
            $table->string('condition_type'); // header, query, body
            $table->string('condition_key');
            $table->string('condition_operator'); // equals, not_equals, contains
            $table->string('condition_value');
            $table->integer('response_status')->default(200);
            $table->json('response_body')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_rules');
    }
};
