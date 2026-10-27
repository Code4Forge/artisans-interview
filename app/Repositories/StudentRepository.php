<?php

namespace App\Repositories;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Enums\RelationshipStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentRepository implements StudentRepositoryInterface
{
    public function queryForUser(User $user): Builder
    {
        return User::query()
            ->role('student')
            ->with(['parents']);
    }

    public function find(int $id): ?User
    {
        return User::query()
            ->role('student')
            ->with(['parents'])
            ->find($id);
    }

    public function create(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $student = User::query()->create($attributes);
            $student->assignRole('student');

            return $student->fresh(['parents']);
        });
    }

    public function update(User $student, array $attributes): User
    {
        if (! array_key_exists('password', $attributes) || blank($attributes['password'])) {
            unset($attributes['password']);
        }

        $student->update($attributes);

        return $student->fresh(['parents']);
    }

    public function delete(User $student): void
    {
        DB::transaction(function () use ($student): void {
            $parentIds = $student->parents()->pluck('users.id')->all();
            $student->parents()->detach();

            $student->delete();

            // Clean up any parents who now have 0 students linked
            foreach ($parentIds as $parentId) {
                $parent = User::find($parentId);
                if ($parent && $parent->children()->count() === 0) {
                    $parent->delete();
                }
            }
        });
    }

    public function syncParents(User $student, array $parents): void
    {
        DB::transaction(function () use ($student, $parents): void {
            $syncData = [];
            $previousParentIds = $student->parents()->pluck('users.id')->all();

            foreach ($parents as $parentData) {
                if (empty($parentData['name']) || empty($parentData['email'])) {
                    continue;
                }

                $parent = User::query()->firstOrCreate(
                    ['email' => strtolower(trim($parentData['email']))],
                    [
                        'name' => trim($parentData['name']),
                        'phone' => ! empty($parentData['phone']) ? trim($parentData['phone']) : null,
                        'password' => Hash::make('password'),
                    ]
                );

                if (! $parent->hasRole('parent')) {
                    $parent->assignRole('parent');
                } else {
                    $parent->syncRolePermissionsDirectly();
                }

                $updatePayload = ['name' => trim($parentData['name'])];
                if (array_key_exists('phone', $parentData)) {
                    $updatePayload['phone'] = ! empty($parentData['phone']) ? trim($parentData['phone']) : null;
                }
                $parent->update($updatePayload);

                $syncData[$parent->id] = [
                    'relationship' => $parentData['relationship'] ?? RelationshipStatus::Guardian->value,
                ];
            }

            $student->parents()->sync($syncData);

            // Clean up old parents who have no remaining children
            $currentParentIds = array_keys($syncData);
            $detachedParentIds = array_diff($previousParentIds, $currentParentIds);

            foreach ($detachedParentIds as $detachedId) {
                $detachedParent = User::find($detachedId);
                if ($detachedParent && $detachedParent->children()->count() === 0) {
                    $detachedParent->delete();
                }
            }
        });
    }
}
