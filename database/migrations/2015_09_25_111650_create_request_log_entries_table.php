<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestLogEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_log_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
            $table->string('method', 4);
            $table->text('path');
            $table->string('ip', 45);
            $table->string('session')->nullable();
            $table->text('get')->nullable();
            $table->text('post')->nullable();
            $table->text('cookies')->nullable();
            $table->string('agent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_log_entries');
    }
}
