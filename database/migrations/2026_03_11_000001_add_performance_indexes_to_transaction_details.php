<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPerformanceIndexesToTransactionDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            // Add index on category_id for JOIN performance
            $table->index('category_id', 'idx_category_id');

            // Add index on customer_id for WHERE clause performance
            $table->index('customer_id', 'idx_customer_id');

            // Add index on status for filtering
            $table->index('status', 'idx_status');

            // Add composite index for the exact query pattern in dashboardCategory
            // This covers: category_id + customer_id + start_date + end_date + status
            $table->index(
                ['customer_id', 'category_id', 'status', 'start_date', 'end_date'],
                'idx_category_lookup'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropIndex('idx_category_id');
            $table->dropIndex('idx_customer_id');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_category_lookup');
        });
    }
}
