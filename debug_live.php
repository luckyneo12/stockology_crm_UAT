<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

$request = Request::capture();
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);

echo "<h1>Laravel Diagnostic</h1>";
echo "<b>APP_URL (env):</b> " . env('APP_URL') . "<br>";
echo "<b>APP_URL (config):</b> " . config('app.url') . "<br>";
echo "<b>APP_ENV (config):</b> " . config('app.env') . "<br>";
echo "<b>Detected Root URL:</b> " . $request->root() . "<br>";
echo "<b>Generated URL (url('/')):</b> " . url('/') . "<br>";
echo "<b>SERVER HTTP_HOST:</b> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<b>Config Cache File Exists:</b> " . (file_exists(__DIR__ . '/bootstrap/cache/config.php') ? 'Yes' : 'No') . "<br>";

if (file_exists(__DIR__ . '/bootstrap/cache/config.php')) {
    echo "<b>Config Cache Content (Partial):</b> <pre>";
    $config = include __DIR__ . '/bootstrap/cache/config.php';
    echo "App URL in Cache: " . ($config['app']['url'] ?? 'Not found');
    echo "</pre>";
}
