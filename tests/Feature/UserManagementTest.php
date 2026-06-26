<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Laravel\Sanctum\Sanctum;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'display_name' => 'Admin']);
        Role::firstOrCreate(['name' => 'doctor', 'display_name' => 'Doctor']);
    }

    public function test_user_can_register()
    {
        $role = Role::where('name', 'doctor')->first();
        
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Doctor',
            'email' => 'doctor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('users', [
            'email' => 'doctor@example.com',
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_accept_user()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $doctorRole = Role::where('name', 'doctor')->first();
        $pendingUser = User::factory()->create([
            'role_id' => $doctorRole->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/admin/users/{$pendingUser->id}/accept");

        $response->assertStatus(200);
        $this->assertEquals('active', $pendingUser->fresh()->status);
    }

    public function test_admin_can_reject_user()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $doctorRole = Role::where('name', 'doctor')->first();
        $pendingUser = User::factory()->create([
            'role_id' => $doctorRole->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/admin/users/{$pendingUser->id}/reject");

        $response->assertStatus(200);
        $this->assertEquals('inactive', $pendingUser->fresh()->status);
    }
}
