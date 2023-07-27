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
            // use this if there's already inserted data on garbage savings table
            $table->unsignedBigInteger('trash_category_id')->index('garbage_savings_datas_trash_category_id_foreign')->after('user_id')->nullable();

            // $table->unsignedBigInteger('trash_category_id')->index('garbage_savings_datas_trash_category_id_foreign')->after('user_id')->nullable();

            $table->foreign(['trash_category_id'])->references(['id'])->on('trash_categories');
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
            $table->dropForeign('garbage_savings_datas_trash_category_foreign');
            $table->dropColumn('trash_category_id');
        });
    }
};
