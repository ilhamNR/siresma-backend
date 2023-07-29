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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('code'); 
            $table->string('type');
            $table->unsignedBigInteger('user_id')->index('transaction_logs_user_id_foreign');
            $table->unsignedBigInteger('garbage_savings_data_id')->index('transaction_logs_garbage_savings_data_id_foreign');
            $table->integer('amount');
            $table->integer('is_approved')->default(0);
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
        Schema::dropIfExists('transaction_logs');
    }
};
