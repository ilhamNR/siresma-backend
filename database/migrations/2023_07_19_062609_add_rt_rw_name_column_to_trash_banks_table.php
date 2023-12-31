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
        Schema::table('trash_banks', function (Blueprint $table) {
            $table->string('rt')->after('name');
            $table->string('rw')->after('rt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trash_banks', function (Blueprint $table) {
            $table->dropColumn('rt');
            $table->dropColumn('rw');
        });
    }
};
