<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('endpoint_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('endpoints')->onDelete('cascade');
            $table->string('method');
            $table->string('url');
            $table->integer('status_code');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoint_calls');
    }
};
