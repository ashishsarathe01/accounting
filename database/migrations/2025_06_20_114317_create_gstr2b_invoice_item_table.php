<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2bInvoiceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2b_invoice_item', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id');
            $table->bigInteger('sparent_id');
            $table->integer('snum');
            $table->integer('rate');
            $table->string('taxable_amount');
            $table->string('igst');
            $table->string('cgst');
            $table->string('sgst');
            $table->string('cess');
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
        Schema::dropIfExists('gstr2b_invoice_item');
    }
}
