<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2aInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2a_invoice', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id');
            $table->string('gst');
            $table->string('inv_date');
            $table->string('inv_val');
            $table->string('inum');
            $table->tinyInteger('status')->deafult(1);
            $table->bigInteger('company_id');
            $table->string('company_gstin');
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
        Schema::dropIfExists('gstr2a_invoice');
    }
}
