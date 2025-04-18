<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('party')->nullable();
            $table->string('tax_cgst')->nullable();
            $table->string('tax_sgst')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('taxable_amt')->nullable();
            $table->string('total')->nullable();
            $table->string('cgst')->nullable();
            $table->string('sgst')->nullable();
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
        Schema::dropIfExists('sales_returns');
    }
}
