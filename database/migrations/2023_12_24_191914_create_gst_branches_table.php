<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gst_branches', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('gst_setting_id')->nullable();
            $table->string('branch_address')->nullable();
            $table->string('branch_city')->nullable();
            $table->string('branch_pincode')->nullable();
            $table->string('branch_matcenter')->nullable();
            $table->string('branch_series')->nullable();
            $table->string('branch_invoice_start_from')->nullable();
            $table->enum('status', ['0', '1'])->default(0);
            $table->enum('delete', ['0', '1'])->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('gst_branches');
    }
}
