<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'Aniket shukla')->first();
if ($user) {
    echo "User found: " . $user->name . " (ID: " . $user->id . ")\n";
    $emp = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
    if ($emp) {
        echo "Employee found: " . $emp->name . " (ID: " . $emp->id . ")\n";
        echo "Department ID: " . $emp->department_id . "\n";
        if ($emp->department) {
            echo "Department Name: " . $emp->department->name . "\n";
        }
        else {
            echo "Department relation is null.\n";
            // Check raw department table
            $dept = \Workdo\Hrm\Entities\Department::find($emp->department_id);
            if ($dept) {
                echo "Department found manually: " . $dept->name . "\n";
            }
            else {
                echo "Department record not found for ID: " . $emp->department_id . "\n";
            }
        }
    }
    else {
        echo "Employee record not found for this user.\n";
    }
}
else {
    echo "User 'Aniket shukla' not found.\n";
}
