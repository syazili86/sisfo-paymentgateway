<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingExpiredTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BillingExpired', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('BillingID');
            $table->integer('PaymentMethodId');
            $table->datetime('expiredDate');
            $table->timestamps();
            $table->unique('BillingID', 'PaymentMethodId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_expired');
    }
}
