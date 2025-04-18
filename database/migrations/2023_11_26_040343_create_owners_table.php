<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('company_id');
            $table->string('owner_name');
            $table->string('father_name');
            $table->string('date_of_birth');
            $table->string('address')->nullable();
            $table->string('pan');
            $table->string('designation')->nullable();
            $table->string('date_of_joining')->nullable();
            $table->string('mobile_no');
            $table->string('email_id');
            $table->string('din')->nullable();
            $table->string('share_percentage')->nullable();
            $table->string('authorized_signatory')->nullable();
            $table->string('date_of_resigning')->nullable();
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
        Schema::dropIfExists('owners');
    }
}
