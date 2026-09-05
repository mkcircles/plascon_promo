<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique index for lookups and deduplication
            $table->string('brand')->nullable()->index(); // Index for filtering by brand
            $table->enum('status', ['pending', 'used'])->default('pending');
            $table->string('inMessageId')->nullable()->index(); // Index if querying by SMS/message ID
            $table->string('prizeWon')->nullable();
            $table->timestamps();

            // Composite index for common redemption/reporting queries: WHERE brand = ? AND status = ?
            $table->index(['brand', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codes');
    }
};
