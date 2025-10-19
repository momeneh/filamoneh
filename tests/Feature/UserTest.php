<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean database state
        \Illuminate\Support\Facades\DB::rollBack();
        
        $this->seed();
    }

    #[Test]
    public function user_can_be_created_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'mobile' => '09123456789'
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '09123456789'
        ]);

        $this->assertTrue(password_verify('password123', $user->password));
    }

    #[Test]
    public function user_can_have_multiple_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['title' => 'Admin']);
        $editorRole = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$adminRole->id, $editorRole->id]);

        $this->assertCount(2, $user->roles()->get());
        $this->assertTrue($user->roles->contains($adminRole));
        $this->assertTrue($user->roles->contains($editorRole));
    }

    #[Test]
    public function user_can_get_permissions_through_roles()
    {
        $user = User::factory()->create();
        $role = Role::create(['title' => 'Admin']);
        
        $permission1 = Permission::create(['name' => 'create_papers']);
        $permission2 = Permission::create(['name' => 'edit_papers']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->roles()->attach($role->id);

        $userPermissions = $user->permissions();

        $this->assertCount(2, $userPermissions);
        $this->assertTrue($user->hasPermission('create_papers'));
        $this->assertTrue($user->hasPermission('edit_papers'));
        $this->assertFalse($user->hasPermission('delete_papers'));
    }

    #[Test]
    public function user_can_have_avatar_url()
    {
        $user = User::factory()->create();

        $this->assertNull($user->getFilamentAvatarUrl());
    }

    

    #[Test]
    public function user_password_is_hashed_when_created()
    {
        $user = User::factory()->create([
            'password' => 'plaintext_password'
        ]);

        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(password_verify('plaintext_password', $user->password));
    }

    #[Test]
    public function user_email_must_be_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }

    #[Test]
    public function user_can_be_authenticated()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $this->assertTrue(auth()->attempt([
            'email' => $user->email,
            'password' => 'password123'
        ]));

        $this->assertAuthenticated();
    }

    #[Test]
    public function user_can_be_created_using_factory()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    #[Test]
    public function user_has_role_user_relationship()
    {
        $user = User::factory()->create();
        $role = Role::create(['title' => 'Test Role']);
        
        $user->roles()->attach($role->id);

        $this->assertCount(1, $user->roleUser()->get());
        $this->assertInstanceOf(\App\Models\RoleUser::class, $user->roleUser->first());
    }
}
