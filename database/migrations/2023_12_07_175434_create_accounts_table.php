<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('account_name')->nullable();
            $table->string('print_name')->nullable();
            $table->string('under_group')->nullable();
            $table->string('under_group_s')->nullable();
            $table->string('opening_balance')->nullable();
            $table->string('dr_cr')->nullable();
            $table->string('address')->nullable();
            $table->string('gstin')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('pan')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('whatsup_number')->nullable();
            $table->string('maintain_bill_by_details')->nullable();
            $table->string('credit_days')->nullable();
            $table->string('limit')->nullable();
            $table->string('price_change_sms')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('depreciation_rate')->nullable();
            $table->string('yearly')->nullable();
            $table->string('per_tax')->nullable();
            $table->string('company_act')->nullable();
            $table->string('gst_rate')->nullable();
            $table->string('hsn_code')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
