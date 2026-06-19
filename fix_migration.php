<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Boot the application kernel to initialize all service providers
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if migration is already marked
    $exists = \Illuminate\Support\Facades\DB::table('migrations')
        ->where('migration', '2019_05_03_000001_create_customers_table')
        ->exists();

    if ($exists) {
        echo "Migration is already marked as run in the database!\n";
    } else {
        \Illuminate\Support\Facades\DB::table('migrations')->insert([
            'migration' => '2019_05_03_000001_create_customers_table',
            'batch' => 50
        ]);
        echo "Successfully marked migration as run in the database!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
