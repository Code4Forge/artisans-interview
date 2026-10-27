<?php

use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('authorizes admin with role permissions and forbids teacher without them', function () {
    $policy = new RolePolicy;
    $admin = User::factory()->withRole('admin')->create();
    $teacher = User::factory()->withRole('teacher')->create();
    $role = Role::findByName('teacher', 'web');

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $role))->toBeTrue()
        ->and($policy->delete($admin, $role))->toBeTrue()
        ->and($policy->managePermissions($admin, $role))->toBeTrue()
        ->and($policy->viewAny($teacher))->toBeFalse()
        ->and($policy->create($teacher))->toBeFalse()
        ->and($policy->update($teacher, $role))->toBeFalse()
        ->and($policy->delete($teacher, $role))->toBeFalse();
});
