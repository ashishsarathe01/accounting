<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstr2bTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gstr2b', function (Blueprint $table){
            $table->id();
            $table->string('ctin');
            $table->string('account_name');
            $table->string('sup_fill_date');
            $table->string('sup_prd');
            $table->tinyInteger('status')->default(1);
            $table->string('company_gstin');
            $table->integer('company_id');
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
        Schema::dropIfExists('gstr2b');
    }
}
