<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_bookmarks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cust_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->timestamps();

            // Add unique constraint to prevent duplicate bookmarks
            $table->unique(['cust_id', 'question_id'], 'unique_customer_question');

            // Add indexes for better query performance
            $table->index('cust_id', 'idx_cust_id');
            $table->index('question_id', 'idx_question_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_bookmarks');
    }
}
