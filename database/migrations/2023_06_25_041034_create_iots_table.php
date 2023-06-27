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
        Schema::create('iots', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->integer('weight');
            $table->foreignId('garbage_savings_data_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iots', function (Blueprint $table) {
            $table->dropForeign(['garbage_savings_data_id']);
        });
        Schema::dropIfExists('iots');
    }
};
