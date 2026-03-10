<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPerformanceIndexesToLevelManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbllevelmanagement', function (Blueprint $table) {
            // Add index on levelId for JOIN performance
            $table->index('levelId', 'idx_level_id');

            // Add index on isActive for filtering
            $table->index('isActive', 'idx_is_active');

            // Add composite index for common query pattern
            $table->index(['isActive', 'levelId'], 'idx_active_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbllevelmanagement', function (Blueprint $table) {
            $table->dropIndex('idx_level_id');
            $table->dropIndex('idx_is_active');
            $table->dropIndex('idx_active_level');
        });
    }
}
