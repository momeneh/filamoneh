<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function role_can_be_created()
    {
        $role = Role::create(['title' => 'Admin']);

        $this->assertDatabaseHas('roles', [
            'title' => 'Admin'
        ]);

        $this->assertInstanceOf(Role::class, $role);
    }

    #[Test]
    public function role_can_have_multiple_users()
    {
        $role = Role::create(['title' => 'Editor']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $role->users()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $role->users()->get());
        $this->assertTrue($role->users->contains($user1));
        $this->assertTrue($role->users->contains($user2));
    }

    #[Test]
    public function role_can_have_multiple_permissions()
    {
        $role = Role::create(['title' => 'Admin']);
        $permission1 = Permission::create(['name' => 'create_papers']);
        $permission2 = Permission::create(['name' => 'edit_papers']);
        $permission3 = Permission::create(['name' => 'delete_papers']);

        $role->permissions()->attach([$permission1->id, $permission2->id, $permission3->id]);

        $this->assertCount(3, $role->permissions()->get());
        $this->assertTrue($role->permissions->contains($permission1));
        $this->assertTrue($role->permissions->contains($permission2));
        $this->assertTrue($role->permissions->contains($permission3));
    }

    #[Test]
    public function permission_can_be_created()
    {
        $permission = Permission::create(['name' => 'manage_users']);

        $this->assertDatabaseHas('permissions', [
            'name' => 'manage_users'
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
    }

    #[Test]
    public function permission_role_pivot_table_works()
    {
        $role = Role::create(['title' => 'Moderator']);
        $permission = Permission::create(['name' => 'moderate_content']);

        $role->permissions()->attach($permission->id);

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id
        ]);
    }

    #[Test]
    public function user_can_have_multiple_roles_with_different_permissions()
    {
        $user = User::factory()->create();
        
        // Create roles
        $adminRole = Role::create(['title' => 'Admin']);
        $editorRole = Role::create(['title' => 'Editor']);
        
        // Create permissions
        $createPermission = Permission::create(['name' => 'create_papers']);
        $editPermission = Permission::create(['name' => 'edit_papers']);
        $deletePermission = Permission::create(['name' => 'delete_papers']);
        
        // Assign permissions to roles
        $adminRole->permissions()->attach([$createPermission->id, $editPermission->id, $deletePermission->id]);
        $editorRole->permissions()->attach([$createPermission->id, $editPermission->id]);
        
        // Assign roles to user
        $user->roles()->attach([$adminRole->id, $editorRole->id]);

        // User should have all permissions from both roles (without duplicates)
        $userPermissions = $user->permissions();
        $this->assertCount(3, $userPermissions); // create, edit, delete
        $this->assertTrue($user->hasPermission('create_papers'));
        $this->assertTrue($user->hasPermission('edit_papers'));
        $this->assertTrue($user->hasPermission('delete_papers'));
    }

    #[Test]
    public function user_permissions_are_unique_across_roles()
    {
        $user = User::factory()->create();
        
        $role1 = Role::create(['title' => 'Role1']);
        $role2 = Role::create(['title' => 'Role2']);
        
        $permission = Permission::create(['name' => 'shared_permission']);
        
        // Both roles have the same permission
        $role1->permissions()->attach($permission->id);
        $role2->permissions()->attach($permission->id);
        
        $user->roles()->attach([$role1->id, $role2->id]);

        // User should have the permission only once
        $userPermissions = $user->permissions();
        $this->assertCount(1, $userPermissions);
        $this->assertTrue($user->hasPermission('shared_permission'));
    }

    #[Test]
    public function role_title_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::create([]);
    }

    #[Test]
    public function permission_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Permission::create([]);
    }

    #[Test]
    public function role_can_be_updated()
    {
        $role = Role::create(['title' => 'Old Title']);
        
        $role->update(['title' => 'New Title']);

        $this->assertEquals('New Title', $role->fresh()->title);
        $this->assertDatabaseHas('roles', ['title' => 'New Title']);
    }

    #[Test]
    public function permission_can_be_updated()
    {
        $permission = Permission::create(['name' => 'old_permission']);
        
        $permission->update(['name' => 'new_permission']);

        $this->assertEquals('new_permission', $permission->fresh()->name);
        $this->assertDatabaseHas('permissions', ['name' => 'new_permission']);
    }

    #[Test]
    public function role_can_be_deleted()
    {
        $role = Role::create(['title' => 'Temporary Role']);
        $roleId = $role->id;
        
        $role->delete();

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    #[Test]
    public function permission_can_be_deleted()
    {
        $permission = Permission::create(['name' => 'temporary_permission']);
        $permissionId = $permission->id;
        
        $permission->delete();

        $this->assertDatabaseMissing('permissions', ['id' => $permissionId]);
    }

    #[Test]
    public function user_can_be_removed_from_role()
    {
        $user = User::factory()->create();
        $role = Role::create(['title' => 'Test Role']);
        
        $user->roles()->attach($role->id);
        $this->assertCount(1, $user->roles()->get());

        $user->roles()->detach($role->id);
        $this->assertCount(0, $user->fresh()->roles()->get());
    }

    #[Test]
    public function permission_can_be_removed_from_role()
    {
        $role = Role::create(['title' => 'Test Role']);
        $permission = Permission::create(['name' => 'test_permission']);
        
        $role->permissions()->attach($permission->id);
        $this->assertCount(1, $role->permissions()->get());

        $role->permissions()->detach($permission->id);
        $this->assertCount(0, $role->fresh()->permissions()->get());
    }

    #[Test]
    public function complex_role_permission_scenario()
    {
        // Create users
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $editor = User::factory()->create(['email' => 'editor@example.com']);
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        
        // Create roles
        $adminRole = Role::create(['title' => 'Administrator']);
        $editorRole = Role::create(['title' => 'Editor']);
        $viewerRole = Role::create(['title' => 'Viewer']);
        
        // Create permissions
        $permissions = [
            'create_papers' => Permission::create(['name' => 'create_papers']),
            'edit_papers' => Permission::create(['name' => 'edit_papers']),
            'delete_papers' => Permission::create(['name' => 'delete_papers']),
            'view_papers' => Permission::create(['name' => 'view_papers']),
            'manage_users' => Permission::create(['name' => 'manage_users']),
        ];
        
        // Assign permissions to roles
        $adminRole->permissions()->attach([
            $permissions['create_papers']->id,
            $permissions['edit_papers']->id,
            $permissions['delete_papers']->id,
            $permissions['view_papers']->id,
            $permissions['manage_users']->id,
        ]);
        
        $editorRole->permissions()->attach([
            $permissions['create_papers']->id,
            $permissions['edit_papers']->id,
            $permissions['view_papers']->id,
        ]);
        
        $viewerRole->permissions()->attach([
            $permissions['view_papers']->id,
        ]);
        
        // Assign roles to users
        $admin->roles()->attach($adminRole->id);
        $editor->roles()->attach($editorRole->id);
        $viewer->roles()->attach($viewerRole->id);
        
        // Test permissions
        $this->assertTrue($admin->hasPermission('create_papers'));
        $this->assertTrue($admin->hasPermission('manage_users'));
        $this->assertCount(5, $admin->permissions());
        
        $this->assertTrue($editor->hasPermission('create_papers'));
        $this->assertTrue($editor->hasPermission('edit_papers'));
        $this->assertFalse($editor->hasPermission('manage_users'));
        $this->assertCount(3, $editor->permissions());
        
        $this->assertTrue($viewer->hasPermission('view_papers'));
        $this->assertFalse($viewer->hasPermission('create_papers'));
        $this->assertCount(1, $viewer->permissions());
    }
}
