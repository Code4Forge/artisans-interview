<?php

namespace App\Services;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(private StudentRepositoryInterface $students) {}

    /**
     * @param  array{name: string, email: string, password: string}  $studentData
     * @param  array<int, array{name: string, email: string, relationship: string}>  $parentsData
     */
    public function create(array $studentData, array $parentsData, User $authUser): User
    {
        return DB::transaction(function () use ($studentData, $parentsData): User {
            $student = $this->students->create($studentData);

            if (! empty($parentsData)) {
                $this->students->syncParents($student, $parentsData);
            }

            return $student->fresh(['parents']);
        });
    }

    /**
     * @param  array{name: string, email?: string, password?: string|null}  $studentData
     * @param  array<int, array{name: string, email: string, relationship: string}>  $parentsData
     */
    public function update(User $student, array $studentData, array $parentsData, User $authUser): User
    {
        return DB::transaction(function () use ($student, $studentData, $parentsData): User {
            $student = $this->students->update($student, $studentData);
            $this->students->syncParents($student, $parentsData);

            return $student->fresh(['parents']);
        });
    }

    public function delete(User $student): void
    {
        $this->students->delete($student);
    }
}
