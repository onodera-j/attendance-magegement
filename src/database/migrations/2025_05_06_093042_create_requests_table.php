<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->time('attendance_in');
            $table->time('attendance_out');
            $table->foreignId('rest_id_1')->nullable();
            $table->time('rest_in_1')->nullable();
            $table->time('rest_out_1')->nullable();
            $table->foreignId('rest_id_2')->nullable();
            $table->time('rest_in_2')->nullable();
            $table->time('rest_out_2')->nullable();
            $table->string('remarks');
            $table->date('requested_at');
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
        Schema::dropIfExists('requests');
    }
}
