<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingCronTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BillingCron', function (Blueprint $table) {
            $table->id();
            $table->datetime('runTime');
            $table->datetime('startTime');
            $table->datetime('endTime');
            $table->integer('PaymentMethodId');
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
        Schema::dropIfExists('briva_cron');
    }
}
