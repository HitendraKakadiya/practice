<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card_shares', function (Blueprint $table) {
            $table->unsignedBigInteger('card_id');
            $table
                ->foreign('card_id')
                ->references('id')
                ->on('storecards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car_shares', function (Blueprint $table) {
            //
        });
    }
}
