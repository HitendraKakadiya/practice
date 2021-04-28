<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoredatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storedatas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('stname');
            $table->string('stlocation');
            $table->string('stcontact');
            $table->timestamps();
        });
    }

    /**
     * $table->unsignedBigInteger('user_id');
            
     * $table->unsignedBigInteger('st_id');
            
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storedatas');
    }
}
