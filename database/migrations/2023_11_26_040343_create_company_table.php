<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('business_type');
            $table->string('company_name');
            $table->string('gst_applicable');
            $table->string('gst')->nullable();
            $table->string('pan');
            $table->string('date_of_incorporation')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country_name')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('current_finacial_year')->nullable();
            $table->string('books_start_from')->nullable();
            $table->string('email_id');
            $table->string('mobile_no');
            $table->enum('default_company', ['0', '1'])->default(0);
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
        Schema::dropIfExists('companies');
    }
}
