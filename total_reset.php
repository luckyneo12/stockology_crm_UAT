<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = \Illuminate\Http\Request::capture());

echo "<h1>CRM Total Reset</h1>";

echo "Clearing Caches...<br>";
Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('view:clear');
Artisan::call('route:clear');
Artisan::call('optimize:clear');
Artisan::call('permission:cache-reset');
Artisan::call('auth:clear-resets');
echo "Caches Cleared Successfully!<br><br>";

echo "Resetting Session...<br>";
Session::flush();
echo "Session Flushed!<br><br>";

echo "Fixing StockMarket Sidebar Cache...<br>";
// Deleting sidebar cache for all users
DB::statement("DELETE FROM cache WHERE `key` LIKE 'sidebar_menu%'");
echo "Sidebar Menu Cache Deleted!<br><br>";

echo "<b>All done!</b> Please go to <a href='/'>Home Page</a> and Login again.";
