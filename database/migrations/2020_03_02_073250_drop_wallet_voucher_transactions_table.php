<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DropWalletVoucherTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('wallet_voucher_transactions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('wallet_voucher_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('token_id')->unsigned();
            $table->integer('fund_provider_id')->unsigned();
            $table->integer('wallet_voucher_id')->unsigned();
            $table->integer('amount');
            $table->string('type', 20);
            $table->string('state', 20);
            $table->timestamps();

            $table->foreign('token_id'
            )->references('id')->on('tokens')->onDelete('cascade');

            $table->foreign('fund_provider_id'
            )->references('id')->on('fund_providers')->onDelete('cascade');

            $table->foreign('wallet_voucher_id'
            )->references('id')->on('wallet_vouchers')->onDelete('cascade');
        });
    }
}
