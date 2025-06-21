<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2aTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2a', function (Blueprint $table) {
            $table->id();
            $table->string('ctin');
            $table->string('fldtr1');
            $table->string('cfs3b');
            $table->string('flprdr1');
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
        Schema::dropIfExists('gstr2a');
    }
}
