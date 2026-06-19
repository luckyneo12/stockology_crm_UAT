<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

try {
    // Check if migration is already marked
    $exists = $app->make('db')->table('migrations')
        ->where('migration', '2019_05_03_000001_create_customers_table')
        ->exists();

    if ($exists) {
        echo "Migration is already marked as run in the database!\n";
    } else {
        $app->make('db')->table('migrations')->insert([
            'migration' => '2019_05_03_000001_create_customers_table',
            'batch' => 50
        ]);
        echo "Successfully marked migration as run in the database!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
