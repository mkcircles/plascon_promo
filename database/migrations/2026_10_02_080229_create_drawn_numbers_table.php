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
        Schema::create('drawn_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn', 32);
            $table->string('file_used', 100)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('draw_type', 50)->default('daily');
            $table->string('prize', 100)->nullable();
            $table->timestamp('drawn_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();

            $table->index('msisdn', 'idx_drawn_msisdn');
            $table->index('region', 'idx_drawn_region');
            $table->index('draw_type', 'idx_drawn_type');
            $table->index('drawn_at', 'idx_drawn_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drawn_numbers');
    }
};
