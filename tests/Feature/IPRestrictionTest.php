<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IPRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_ip_restriction_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'type' => 'staff',
            'is_enable_login' => 1,
            'is_disable' => 0,
            'allowed_login_ips' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_correct_ip_restriction_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'type' => 'staff',
            'is_enable_login' => 1,
            'is_disable' => 0,
            'allowed_login_ips' => '127.0.0.1',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_incorrect_ip_restriction_cannot_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'type' => 'staff',
            'is_enable_login' => 1,
            'is_disable' => 0,
            'allowed_login_ips' => '192.168.1.1',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_with_role_ip_restriction_cannot_login_from_wrong_ip()
    {
        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
            'allowed_login_ips' => '192.168.1.1',
        ]);

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'type' => 'test_role',
            'is_enable_login' => 1,
            'is_disable' => 0,
        ]);
        $user->addRole($role);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
