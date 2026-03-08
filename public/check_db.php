<?php
// Check what tables exist in the Railway database
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain');

try {
    echo "Checking Railway Database...\n";
    echo "===========================\n\n";

    // Test connection
    DB::connection()->getPdo();
    echo "✓ Database connection successful!\n\n";

    // Get all tables
    $tables = DB::select('SHOW TABLES');
    $tableCount = count($tables);

    if ($tableCount > 0) {
        echo "Tables found ($tableCount):\n";
        echo "------------------------\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // Get row count for each table
            $count = DB::table($tableName)->count();
            echo sprintf("%-40s %d rows\n", $tableName, $count);
        }
    } else {
        echo "❌ NO TABLES FOUND!\n\n";
        echo "You need to import your database schema.\n";
        echo "The migrations only created 'customer_bookmarks' table.\n\n";
        echo "Required tables based on your models:\n";
        echo "- tblcustomerregister\n";
        echo "- subject\n";
        echo "- tblquestion\n";
        echo "- levelmanagement\n";
        echo "- topics\n";
        echo "- banner\n";
        echo "- subscription_plan\n";
        echo "- user_subscription_plan\n";
        echo "- and many more...\n\n";
        echo "Please import your full database dump using TablePlus.\n";
    }

} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
