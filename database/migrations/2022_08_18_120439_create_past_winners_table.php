<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('past_winners', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn');
            $table->string('addedBy');
            $table->enum('category',['winner','blacklisted'])->default('winner');
            $table->timestamps();
        });
        Artisan::call('db:seed', [
            '--class' => 'BlacklistSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('past_winners');
    }
};
