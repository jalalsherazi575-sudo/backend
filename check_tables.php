<?php
// Check what tables exist in the Railway database
$host = 'tramway.proxy.rlwy.net';
$port = '51549';
$dbname = 'railway';
$username = 'root';
$password = 'QaPVFpRVfpVOouGLQBllSPmkjUnyxgeO';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to Railway database successfully!\n\n";

    // Show all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "Tables found in database:\n";
        echo "========================\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
        echo "\nTotal tables: " . count($tables) . "\n";
    } else {
        echo "No tables found in database!\n";
        echo "\nYou need to import your database schema manually using TablePlus or phpMyAdmin.\n";
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
