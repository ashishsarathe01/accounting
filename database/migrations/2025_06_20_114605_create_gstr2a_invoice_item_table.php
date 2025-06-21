<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2aInvoiceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2a_invoice_item', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id');
            $table->string('taxable_val');
            $table->string('rate');
            $table->string('igst');
            $table->string('cgst');
            $table->string('sgst');
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
        Schema::dropIfExists('gstr2a_invoice_item');
    }
}
