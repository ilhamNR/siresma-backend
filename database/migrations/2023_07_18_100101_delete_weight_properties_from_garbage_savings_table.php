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
        Schema::table('garbage_savings_datas', function (Blueprint $table) {
            $table->dropColumn('weight');
            $table->dropColumn('generated_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('garbage_savings_datas', function (Blueprint $table) {
            $table->float('weight')->nullable();
            $table->string('generated_code');
        });
    }
};
