<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('allows an admin to view the roles listing', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Roles')
        ->assertSee('Add Role');
});

it('loads roles via ajax datatable', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->getJson(route('admin.roles.index'), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSee('admin')
        ->assertSee('teacher');
});

it('allows an admin to open create role page', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->get(route('admin.roles.create'))
        ->assertOk()
        ->assertSee('Add New Role');
});

it('allows an admin to create a new role', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->post(route('admin.roles.store'), [
            'name' => 'manager',
        ])
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::where('name', 'manager')->exists())->toBeTrue();
});

it('allows an admin to open edit role page', function () {
    $admin = User::factory()->withRole('admin')->create();
    $teacherRole = Role::findByName('teacher', 'web');

    $this->actingAs($admin)
        ->get(route('admin.roles.edit', $teacherRole))
        ->assertOk()
        ->assertSee('Edit Role')
        ->assertSee('teacher');
});

it('prevents deleting a role assigned to users', function () {
    $admin = User::factory()->withRole('admin')->create();
    $teacher = User::factory()->withRole('teacher')->create();
    $teacherRole = Role::findByName('teacher', 'web');

    $this->actingAs($admin)
        ->deleteJson(route('admin.roles.destroy', $teacherRole))
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect(Role::where('name', 'teacher')->exists())->toBeTrue();
});

it('allows deleting a role when no users are assigned', function () {
    $admin = User::factory()->withRole('admin')->create();
    $customRole = Role::create(['name' => 'unassigned_role', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->deleteJson(route('admin.roles.destroy', $customRole))
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Role::where('name', 'unassigned_role')->exists())->toBeFalse();
});

it('allows an admin to view and update permissions for a role', function () {
    $admin = User::factory()->withRole('admin')->create();
    $teacherRole = Role::findByName('teacher', 'web');

    $this->actingAs($admin)
        ->get(route('admin.roles.permissions', $teacherRole))
        ->assertOk()
        ->assertSee("Teacher Role's Permissions");

    $this->actingAs($admin)
        ->putJson(route('admin.roles.permissions.update', $teacherRole), [
            'permissions' => ['dashboard.view', 'teachers.view', 'students.view'],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $teacherRole->refresh();

    expect($teacherRole->hasPermissionTo('teachers.view'))->toBeTrue()
        ->and($teacherRole->hasPermissionTo('students.view'))->toBeTrue()
        ->and($teacherRole->hasPermissionTo('teachers.create'))->toBeFalse();
});

it('seeds flat permissions and default roles in RolePermissionSeeder', function () {
    (new RolePermissionSeeder)->run();

    foreach (RolePermissionSeeder::PERMISSIONS as $perm) {
        $this->assertDatabaseHas('permissions', ['name' => $perm, 'guard_name' => 'web']);
    }

    foreach (array_keys(RolePermissionSeeder::ROLES) as $roleName) {
        $this->assertDatabaseHas('roles', ['name' => $roleName, 'guard_name' => 'web']);
    }
});

it('auto-applies all role permissions directly to newly created user when assigned to a role', function () {
    (new RolePermissionSeeder)->run();

    // Test with student role
    $studentRole = Role::findByName('student', 'web');
    $studentRolePermissions = $studentRole->permissions->pluck('name')->sort()->values()->toArray();

    $john = User::factory()->create(['name' => 'John']);
    $john->assignRole('student');

    $johnDirectPermissions = $john->permissions()->pluck('name')->sort()->values()->toArray();

    expect($johnDirectPermissions)->not->toBeEmpty()
        ->and($johnDirectPermissions)->toEqual($studentRolePermissions);

    foreach ($studentRolePermissions as $permName) {
        expect($john->hasDirectPermission($permName))->toBeTrue();
    }

    // Test with teacher role
    $teacherRole = Role::findByName('teacher', 'web');
    $teacherRolePermissions = $teacherRole->permissions->pluck('name')->sort()->values()->toArray();

    $jane = User::factory()->create(['name' => 'Jane']);
    $jane->assignRole('teacher');

    $janeDirectPermissions = $jane->permissions()->pluck('name')->sort()->values()->toArray();

    expect($janeDirectPermissions)->not->toBeEmpty()
        ->and($janeDirectPermissions)->toEqual($teacherRolePermissions);

    foreach ($teacherRolePermissions as $permName) {
        expect($jane->hasDirectPermission($permName))->toBeTrue();
    }
});

it('auto-syncs direct permissions to users when role permissions are updated', function () {
    (new RolePermissionSeeder)->run();

    $student = User::factory()->create(['name' => 'Alex']);
    $student->assignRole('student');

    $studentRole = Role::findByName('student', 'web');

    $roleService = app(\App\Services\RoleService::class);
    $roleService->updatePermissions($studentRole, ['dashboard.view']);

    $studentDirectPermissions = $student->fresh()->permissions()->pluck('name')->values()->toArray();
    expect($studentDirectPermissions)->toEqual(['dashboard.view']);
});
