<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2bInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2b_invoice', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id');
            $table->string('account_name');
            $table->string('account_gstin');
            $table->string('invoice_no');
            $table->string('type');
            $table->string('idate');
            $table->string('amount');
            $table->string('srctyp');
            $table->string('irn');
            $table->string('irngendate');
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
        Schema::dropIfExists('gstr2b_invoice');
    }
}
