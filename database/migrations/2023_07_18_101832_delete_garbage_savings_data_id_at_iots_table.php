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
        Schema::table('iots', function (Blueprint $table) {
            $table->dropForeign('iots_garbage_savings_data_id_foreign');
            $table->dropColumn('garbage_savings_data_id');
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
            $table->unsignedBigInteger('garbage_savings_data_id')->nullable()->index('iots_garbage_savings_data_id_foreign')->after('weight');
            $table->foreign(['garbage_savings_data_id'])->references(['id'])->on('garbage_savings_datas');
        });
    }
};
