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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('password', 255);
            $table->longText('avatar');
            $table->string('display_name', 55);
            $table->string('phone_number', 15);
            $table->tinyInteger('role_id');
            $table->boolean('status')->default(0);
            $table->boolean('has_edit');
            $table->boolean('verify');
            $table->string('detail_address', 1500);
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
        Schema::dropIfExists('users');
    }
};
