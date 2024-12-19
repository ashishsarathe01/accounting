<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gst_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gst_type')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('business_type')->nullable();
            $table->string('validity_from')->nullable();
            $table->string('validity_to')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('scheme')->nullable();
            $table->string('gst_certificate')->nullable();
            $table->enum('einvoice', ['0', '1'])->default(0);
            $table->string('einvoice_username')->nullable();
            $table->string('einvoice_password')->nullable();
            $table->enum('ewaybill', ['0', '1'])->default(0);
            $table->string('ewaybill_username')->nullable();
            $table->string('ewaybill_password')->nullable();
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
        Schema::dropIfExists('gst_settings');
    }
}
