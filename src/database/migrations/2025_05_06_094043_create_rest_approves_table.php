<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestApprovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_approves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approve_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rest_id')->constrained()->nullable();
            $table->foreignId('rest_request_id')->constrained()->unique();
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
        Schema::dropIfExists('rest_approves');
    }
}
