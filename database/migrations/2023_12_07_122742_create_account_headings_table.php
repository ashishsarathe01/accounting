<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountHeadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_headings', function (Blueprint $table) {

            $table->id();
            $table->integer('company_id');
            $table->string('name');
            $table->string('bs_profile');
            $table->string('name_sch_three');
            $table->string('bs_profile_three');
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
        Schema::dropIfExists('account_headings');
    }
}
