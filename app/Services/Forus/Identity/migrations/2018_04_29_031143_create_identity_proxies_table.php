<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdentityProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identity_proxies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity_address', 200)->nullable();
            $table->string('access_token', 200)->nullable();
            $table->string('auth_token', 64)->nullable();
            $table->string('auth_code', 64)->nullable();
            $table->string('auth_email_token', 64)->nullable();
            $table->string('state', 20);
            $table->integer('expires_in');
            $table->timestamps();

            $table->foreign('identity_address'
            )->references('address')->on('identities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('identity_proxies');
    }
}
