<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Target;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TargetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_cannot_assign_master_target_to_individual()
    {
        // Find a company admin
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        $response = $this->actingAs($admin)
            ->post(route('targets.store'), [
                'target_name' => 'Master Target for Individual Test',
                'assignment_type' => 'individual',
                'assigned_to' => [$admin->id],
                'target_value' => 10,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'target_type' => 'manual',
            ]);

        $response->assertSessionHas('error', 'Admins can only assign targets to Departments or Teams.');
    }

    public function test_admin_can_assign_sub_target_to_individual()
    {
        // Find a company admin
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        // Create a parent/master target first (assigned to a team)
        $parentTarget = Target::create([
            'target_name' => 'Parent Team Target',
            'assigned_to' => 0,
            'department_id' => 0,
            'team_id' => 1,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 100,
            'achieved_value' => 0,
            'status' => 'Pending',
            'workspace' => $admin->active_workspace ?? 1,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Submit sub-target assignment to individual under parent target
        $response = $this->actingAs($admin)
            ->post(route('targets.store'), [
                'parent_id' => $parentTarget->id,
                'target_name' => 'Sub Individual Target',
                'assignment_type' => 'individual',
                'individual_targets' => [
                    $admin->id => 10
                ],
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'target_type' => 'manual',
            ]);

        // It should redirect to targets index on success, and NOT have "Admins can only assign targets" error in session
        $response->assertRedirect(route('targets.index'));
        $response->assertSessionHasNoErrors();
        
        // Assert target was created
        $this->assertTrue(Target::where('parent_id', $parentTarget->id)->where('assigned_to', $admin->id)->exists());
    }

    public function test_admin_can_update_sub_target_assigned_to_individual()
    {
        // Find a company admin
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        // Create a parent target
        $parentTarget = Target::create([
            'target_name' => 'Parent Team Target',
            'assigned_to' => 0,
            'department_id' => 0,
            'team_id' => 1,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 100,
            'achieved_value' => 0,
            'status' => 'Pending',
            'workspace' => $admin->active_workspace ?? 1,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Create a sub-target assigned to individual
        $subTarget = Target::create([
            'parent_id' => $parentTarget->id,
            'target_name' => 'Sub Individual Target',
            'assigned_to' => $admin->id,
            'department_id' => 0,
            'team_id' => 0,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 10,
            'achieved_value' => 0,
            'status' => 'Pending',
            'workspace' => $admin->active_workspace ?? 1,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Try to update it (still keeping it assigned to individual)
        $response = $this->actingAs($admin)
            ->put(route('targets.update', $subTarget->id), [
                'target_name' => 'Updated Sub Target Name',
                'assignment_type' => 'individual',
                'assigned_to' => $admin->id,
                'target_value' => 15,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'target_type' => 'manual',
                'status' => 'Pending',
            ]);

        // It should redirect to targets index and NOT have the restriction error
        $response->assertRedirect(route('targets.index'));
        $response->assertSessionHasNoErrors();
        
        $this->assertEquals(15, $subTarget->fresh()->target_value);
    }

    public function test_unit_performance_scoping_by_hierarchy()
    {
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        $workspaceId = $admin->active_workspace ?? 1;

        if (!class_exists('\Workdo\Hrm\Entities\Department') || !class_exists('\Workdo\Hrm\Entities\Employee')) {
            $this->markTestSkipped('HRM entity classes not found.');
        }

        // Create Department A
        $deptA = \Workdo\Hrm\Entities\Department::create([
            'name' => 'Dept A ' . time(),
            'type' => 'department',
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'branch_id' => 1,
        ]);

        // Create Team A under Dept A
        $teamA = \Workdo\Hrm\Entities\Department::create([
            'name' => 'Team A ' . time(),
            'type' => 'team',
            'parent_id' => $deptA->id,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'branch_id' => 1,
        ]);

        $employeeRole = \App\Models\Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
            'created_by' => $admin->id,
        ]);

        // Create Dept Head User & Employee
        $deptHeadUser = User::create([
            'name' => 'Dept Head User',
            'email' => 'depthead_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'workspace_id' => $workspaceId,
            'active_workspace' => $workspaceId,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);
        $deptHeadUser->addRole($employeeRole);

        $deptHeadEmp = \Workdo\Hrm\Entities\Employee::create([
            'user_id' => $deptHeadUser->id,
            'name' => 'Dept Head',
            'email' => $deptHeadUser->email,
            'department_id' => $deptA->id,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'employee_id' => rand(1000, 9999),
        ]);
        $deptA->manager_id = $deptHeadEmp->id;
        $deptA->save();

        // Create Team Lead User & Employee
        $teamLeadUser = User::create([
            'name' => 'Team Lead User',
            'email' => 'teamlead_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'workspace_id' => $workspaceId,
            'active_workspace' => $workspaceId,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);
        $teamLeadUser->addRole($employeeRole);

        $teamLeadEmp = \Workdo\Hrm\Entities\Employee::create([
            'user_id' => $teamLeadUser->id,
            'name' => 'Team Lead',
            'email' => $teamLeadUser->email,
            'department_id' => $teamA->id,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'employee_id' => rand(1000, 9999),
        ]);
        $teamA->manager_id = $teamLeadEmp->id;
        $teamA->save();

        // Create Regular Member User & Employee
        $memberUser = User::create([
            'name' => 'Member User',
            'email' => 'member_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'workspace_id' => $workspaceId,
            'active_workspace' => $workspaceId,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);
        $memberUser->addRole($employeeRole);
        $memberEmp = \Workdo\Hrm\Entities\Employee::create([
            'user_id' => $memberUser->id,
            'name' => 'Member User',
            'email' => $memberUser->email,
            'department_id' => $teamA->id,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'employee_id' => rand(1000, 9999),
        ]);

        // Assign Target to Dept A
        $targetA = Target::create([
            'target_name' => 'Dept Target',
            'assigned_to' => 0,
            'department_id' => $deptA->id,
            'team_id' => 0,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 100,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Assign Target to Team A
        $targetB = Target::create([
            'parent_id' => $targetA->id,
            'target_name' => 'Team Target',
            'assigned_to' => 0,
            'department_id' => 0,
            'team_id' => $teamA->id,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 50,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Assign Target to Member
        $targetC = Target::create([
            'parent_id' => $targetB->id,
            'target_name' => 'Member Target',
            'assigned_to' => $memberUser->id,
            'department_id' => 0,
            'team_id' => 0,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 10,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // 1. Admin Index View: unitPerformance should contain Dept A (type == 'department')
        $response = $this->actingAs($admin)->get(route('targets.index'));
        $unitPerformance = $response->viewData('unitPerformance') ?? [];
        $deptIds = collect($unitPerformance)->where('type', 'department')->pluck('id')->toArray();
        $this->assertContains($deptA->id, $deptIds);

        // 2. Dept Head Index View: unitPerformance should contain Team A (type == 'team')
        $response = $this->actingAs($deptHeadUser)->get(route('targets.index'));
        $unitPerformance = $response->viewData('unitPerformance') ?? [];
        $teamIds = collect($unitPerformance)->where('type', 'team')->pluck('id')->toArray();
        $this->assertContains($teamA->id, $teamIds);
        
        $myDeptTarget = $response->viewData('myDeptTarget');
        $this->assertNotNull($myDeptTarget);
        $this->assertEquals($targetA->id, $myDeptTarget->id);

        // 3. Team Lead Index View: unitPerformance should contain Member User (type == 'member')
        $response = $this->actingAs($teamLeadUser)->get(route('targets.index'));
        $unitPerformance = $response->viewData('unitPerformance') ?? [];
        $memberIds = collect($unitPerformance)->where('type', 'member')->pluck('id')->toArray();
        $this->assertContains($memberUser->id, $memberIds);

        $myTeamTarget = $response->viewData('myTeamTarget');
        $this->assertNotNull($myTeamTarget);
        $this->assertEquals($targetB->id, $myTeamTarget->id);

        // 4. Regular Member Index View: unitPerformance should contain only Member User themselves
        $response = $this->actingAs($memberUser)->get(route('targets.index'));
        $unitPerformance = $response->viewData('unitPerformance') ?? [];
        $memberIds = collect($unitPerformance)->where('type', 'member')->pluck('id')->toArray();
        $this->assertCount(1, $unitPerformance);
        $this->assertEquals($memberUser->id, $unitPerformance[0]['id']);
    }

    public function test_incentive_calculations_and_ledger()
    {
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        $workspaceId = $admin->active_workspace ?? 1;

        Target::query()->delete();

        // Create a Team Target with an incentive of 500.00
        $teamTarget = Target::create([
            'target_name' => 'Team Sales Quota',
            'assigned_to' => 0,
            'department_id' => 0,
            'team_id' => 1,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 10,
            'incentive' => 500.00,
            'achieved_value' => 0,
            'status' => 'Pending',
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        $this->assertEquals(500.00, $teamTarget->incentive);

        // Fetch index and assert stats contains 500.00 pending and 0.00 earned
        $response = $this->actingAs($admin)->get(route('targets.index'));
        $stats = $response->viewData('stats');
        $this->assertEquals(500.00, $stats['pending_incentive']);
        $this->assertEquals(0.00, $stats['earned_incentive']);

        // Assert team ledger has the pending incentive
        $teamLedger = $response->viewData('teamLedger');
        $this->assertArrayHasKey(1, $teamLedger);
        $this->assertEquals(500.00, $teamLedger[1]['pending']);
        $this->assertEquals(0.00, $teamLedger[1]['earned']);

        // Create a Member Target with an incentive of 200.00 and mark it completed
        $memberTarget = Target::create([
            'parent_id' => $teamTarget->id,
            'target_name' => 'Member Personal Quota',
            'assigned_to' => $admin->id,
            'department_id' => 0,
            'team_id' => 0,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 5,
            'incentive' => 200.00,
            'achieved_value' => 5,
            'status' => 'Completed',
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Fetch index and assert completed target is accounted as earned
        $response = $this->actingAs($admin)->get(route('targets.index'));
        $stats = $response->viewData('stats');
        $this->assertEquals(500.00, $stats['pending_incentive']);
        $this->assertEquals(200.00, $stats['earned_incentive']);

        // Assert member ledger has 200.00 earned
        $memberLedger = $response->viewData('memberLedger');
        $this->assertArrayHasKey($admin->id, $memberLedger);
        $this->assertEquals(0.00, $memberLedger[$admin->id]['pending']);
        $this->assertEquals(200.00, $memberLedger[$admin->id]['earned']);
    }

    public function test_duplicate_monthly_target_prevention()
    {
        $admin = User::where('type', 'company')->first();
        if (!$admin) {
            $this->markTestSkipped('No company admin user found in database.');
        }

        $workspaceId = $admin->active_workspace ?? 1;

        Target::query()->delete();

        // Create initial target assigned to Department 1 for June 2026
        Target::create([
            'target_name' => 'Monthly Lead Generation',
            'assigned_to' => 0,
            'department_id' => 1,
            'team_id' => 0,
            'assigned_by' => $admin->id,
            'responsible_user_id' => $admin->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'target_value' => 20,
            'workspace' => $workspaceId,
            'created_by' => $admin->id,
            'target_type' => 'manual',
        ]);

        // Attempting to assign another manual target with SAME name to SAME department for June 2026 should fail
        $response = $this->actingAs($admin)
            ->post(route('targets.store'), [
                'target_name' => 'Monthly Lead Generation',
                'assignment_type' => 'department',
                'department_targets' => [
                    1 => 15
                ],
                'start_date' => '2026-06-05',
                'end_date' => '2026-06-25',
                'target_type' => 'manual',
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('is already assigned', session('error'));

        // Assigning to a DIFFERENT department or DIFFERENT month (July 2026) should succeed
        $responseSuccess = $this->actingAs($admin)
            ->post(route('targets.store'), [
                'target_name' => 'Monthly Lead Generation',
                'assignment_type' => 'department',
                'department_targets' => [
                    1 => 15
                ],
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
                'target_type' => 'manual',
            ]);

        $responseSuccess->assertRedirect(route('targets.index'));
        $responseSuccess->assertSessionHasNoErrors();
    }
}
