<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manage_items', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('name');
            $table->string('p_name')->nullable();
            $table->string('g_name')->nullable();
            $table->string('u_name')->nullable();
            $table->string('opening_balance_cr')->nullable();
            $table->string('opening_balance_qt_type')->nullable();
            $table->string('opening_balance_type')->nullable();
            $table->string('opening_balance')->nullable();
            $table->string('opening_balance_qty')->nullable();
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
        Schema::dropIfExists('manage_items');
    }
}
