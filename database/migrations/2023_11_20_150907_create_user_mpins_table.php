<?php
  
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
  
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mpins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('mpin');
            $table->string('device_id');
            $table->string('device_type');
            $table->string('device_name');
            $table->timestamp('expire_at')->nullable();
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
        Schema::dropIfExists('user_mpins');
    }
};