<?php

namespace Workdo\Hrm\Listeners;

use App\Events\DestroyUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Hrm\Entities\Employee;

class UserDestroy
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DestroyUser $event)
    {
        $user = $event->user;
        // Delete employee record associated with the user
        $employees = Employee::where('user_id', $user->id)->get();
        foreach ($employees as $employee) {
            \Workdo\Hrm\Entities\Department::where('manager_id', $employee->id)->update(['manager_id' => null]);
            $employee->delete();
        }
    }
}
