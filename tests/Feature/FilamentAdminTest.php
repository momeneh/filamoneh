<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Paper;
use App\Models\Person;
use App\Models\Country;
use App\Models\Role;
use App\Models\Permission;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function admin_can_access_filament_dashboard()
    {
        
       $admin = $this->createAdminUser();
       
        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_access_resource()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/people');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_admin()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    #[Test]
    public function admin_can_view_users_list()
    {
        $admin = $this->createAdminUser();
        
        // Create some test users
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_new_user()
    {
        $admin = $this->createAdminUser();
        
        // Create a test role to ensure it exists
        $role = Role::create(['title' => 'Test Role']);

        $this->actingAs($admin);
        livewire::test(\App\Filament\Resources\UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'mobile' => '09123456789',
            'roles'=>[$role->id]
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ;

        $this->assertDatabaseHas(User::class, [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'mobile' => '09123456789'
        ]);
    }

    #[Test]
    public function admin_can_edit_existing_user()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['name' => 'Old Name']);
        $role = Role::create(['title' => 'Test Role']);
        $this->actingAs($admin);
        $response = livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, [
            'record' => $user->id,
        ])
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'mobile' => '09123456789',
            'roles'=>[$role->id]
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ;
        // dump($response->status());
        // Debug validation errors if any
        if ($response->status() !== 200 && $response->status() !== 302) {
            // dump('Response status:', $response->status());
            // dump('Response content:', $response->getContent());
        }
        $this->assertDatabaseHas(User::class, [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'mobile' => '09123456789'
        ]);
    }

    #[Test]
    public function admin_can_delete_user()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['name' => 'Old Name']);
        $this->actingAs($admin);
       
        $response = livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, [
            'record' => $user->id,
        ])
        ->callAction(\Filament\Pages\Actions\DeleteAction::class)
            ;
    
        $this->assertModelMissing($user);

    }

    

    #[Test]
    public function admin_can_create_new_paper()
    {
        $admin = $this->createAdminUser();
        $country = Country::first() ?? Country::create(['title'=>'test']);

        $paperData = [
            'title' => 'New Research Paper',
            'country_id' => $country->id,
            'abstract' => 'This is a test abstract.',
            'description' => 'This is a test description.',
            'insert_user_id' => $admin->id,
            'edit_user_id' => $admin->id,
            'paperResource'=>[
                [
                    'title'=>'resource 1',
                    'link' => 'link1' 
                ]
            ]
        ];

        $this->actingAs($admin);
        livewire::test(\App\Filament\Resources\PaperResource\Pages\CreatePaper::class)
        ->set('data.paperResource', null)
        ->fillForm($paperData)
        ->call('create')
        ->assertHasNoFormErrors()
        ;

        $this->assertDatabaseHas(Paper::class, [
            'title' => 'New Research Paper',
            'country_id' => $country->id,
            'abstract' => 'This is a test abstract.',
        ]);
        $paper = Paper::where('title', 'New Research Paper')->first();
        $this->assertDatabaseHas('paper_resources', [
            'paper_id' => $paper->id,
            'title' => 'resource 1',
            'link' => 'link1'
        ]);
    }

    #[Test]
    public function admin_can_edit_existing_paper()
    {
        $admin = $this->createAdminUser();
        $country = Country::first() ?? Country::create(['title'=>'test']);
        $paperData = [
            'title' => 'New Research Paper',
            'country_id' => $country->id,
            'abstract' => 'This is a test abstract.',
            'description' => 'This is a test description.',
            'insert_user_id' => $admin->id,
            'edit_user_id' => $admin->id
        ];
        $paper = Paper::create($paperData);

        $this->actingAs($admin);
        $paperData['title'] = 'edited paper';
        $response = livewire::test(\App\Filament\Resources\PaperResource\Pages\EditPaper::class, [
            'record' => $paper->id,
        ])
        ->fillForm($paperData)
        ->call('save')
        ->assertHasNoFormErrors()
        ;
        // dump($response->status());
        // Debug validation errors if any
        if ($response->status() !== 200 && $response->status() !== 302) {
            // dump('Response status:', $response->status());
            // dump('Response content:', $response->getContent());
        }
        $this->assertDatabaseHas(Paper::class, $paperData);
    }

    #[Test]
    public function admin_can_view_people_list()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);
        
        // Create some test people
        Person::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/people');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_new_person()
    {
        $admin = $this->createAdminUser();

        $personData = [
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john.doe@example.com',
            'national_code' => '0076177122',
            'mobile' => '09123456789',
            'gender' => 1,
            'PersonExperience' => [
                [
                    'job_title' => 'Software Developer',
                    'job_start_year' => '1400',
                    'job_end_date' => '1402',
                    'center_id' => null
                ]
            ]
        ];
        $this->actingAs($admin);
        
         livewire::test(\App\Filament\Resources\PersonResource\Pages\CreatePerson::class)
            ->set('data.PersonExperience', null)
            ->fillForm($personData)
            ->call('create')
            ->assertHasNoFormErrors()
        ;

        $this->assertDatabaseHas('people', [
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);
        $person = Person::where('email', 'john.doe@example.com')->first();

        $this->assertDatabaseHas('person_experiences', [
            'person_id' => $person->id,
            'job_title' => 'Software Developer',
            'job_start_year' => '1400'
        ]);
    }

  

    #[Test]
    public function admin_can_manage_roles()
    {
        $admin = $this->createAdminUser();

        // Create a new role
        $response = $this->actingAs($admin);
        livewire::test(\App\Filament\Resources\RoleResource\Pages\CreateRole::class)
        ->fillForm([
            'title' => 'New Role'
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ;

        $this->assertDatabaseHas('roles', ['title' => 'New Role']);

        // Edit the role
        $role = Role::where('title', 'New Role')->first();
        livewire::test(\App\Filament\Resources\RoleResource\Pages\EditRole::class,['record' => $role->id])
        ->fillForm([
            'title' => 'Updated Role'
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'title' => 'Updated Role'
        ]);
    }

  

    #[Test]
    public function admin_can_assign_roles_to_users()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();
        $role = Role::create(['title' => 'Editor']);
        $this->actingAs($admin);

        livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class,['record' => $user->id])
        ->fillForm([
            'roles'=>[$role->id]
        ])
        ->call('save')
        ->assertHasNoFormErrors();

      
        $this->assertTrue($user->fresh()->roles->contains($role));
    }

    #[Test]
    public function admin_can_assign_permissions_to_roles()
    {
        $admin = $this->createAdminUser();
        $role = Role::create(['title' => 'Editor']);
        $permission = Permission::where('name' , 'permission_role.viewAny')->first();
        $this->actingAs($admin);
        $response = livewire::test(\App\Filament\Resources\PermissionRoleResource\Pages\CreatePermissionRole::class)
        ->fillForm([
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ;
      

        $this->assertTrue($role->fresh()->permissions->contains($permission));
    }

    

    #[Test]
    public function admin_can_search_and_filter_data()
    {
        $admin = $this->createAdminUser();
        
        $users = User::all();
        $count = $users->count() > 10 ? 10 : $users->count();
        $this->actingAs($admin);
      

        $esearch =  $users->first()->email;
        livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->searchTable($esearch)
        ->assertCanSeeTableRecords($users->where('email', $esearch))
        ->assertCanNotSeeTableRecords($users->where('email', '!=', $esearch));

    }

    #[Test]
    public function admin_can_bulk_operations()
    {
        $admin = $this->createAdminUser();
        
        // Create multiple users
        $users = User::factory()->count(3)->create();

        $userIds = $users->pluck('id')->toArray();

        $this->actingAs($admin);
        $response = livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $users);

        foreach ($userIds as $userId) {
            $this->assertDatabaseMissing('users', ['id' => $userId]);
        }
    }

  

  

    
     
}
