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
            $table->string('status')->after('user_id')->default("ON_PROCESS");
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
            $table->dropColumn('status');
         });
    }
};
