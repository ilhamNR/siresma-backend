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
            $table->unsignedBigInteger('iot_id')->nullable()->index('garbage_savings_datas_iot_id_foreign')->after('trash_category');
            $table->foreign(['iot_id'])->references(['id'])->on('iots');
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
            $table->dropForeign('garbage_savings_datas_iot_id_foreign');
            $table->dropColumn('iot_id');
        });
    }
};
