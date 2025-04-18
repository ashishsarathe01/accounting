<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('series_no')->nullable();
            $table->string('date')->nullable();
            $table->string('voucher_no')->nullable();
            $table->string('party')->nullable();
            $table->string('material_center')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('taxable_amt')->nullable();
            $table->string('tax')->nullable();
            $table->string('total')->nullable();
            $table->string('self_vehicle')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('invoice_date')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
