<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillSundrysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_sundrys', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('name')->nullable();
            $table->string('bill_sundry_type')->nullable();
            $table->string('adjust_sale_amt')->nullable();
            $table->string('sale_amt_account')->nullable();
            $table->string('adjust_purchase_amt')->nullable();
            $table->string('purchase_amt_account')->nullable();
            $table->enum('status', ['0', '1'])->default(0);
            $table->enum('delete', ['0', '1'])->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_sundrys');
    }
}
