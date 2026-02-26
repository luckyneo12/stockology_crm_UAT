<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;

$users = User::select('id', 'name', 'type', 'created_by')->take(10)->get();

echo "ID | Name | Type | Created By\n";
echo "---|------|------|-----------\n";
foreach ($users as $user) {
    echo "{$user->id} | {$user->name} | {$user->type} | {$user->created_by}\n";
}
