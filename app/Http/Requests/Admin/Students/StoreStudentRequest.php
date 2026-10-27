<?php

namespace App\Http\Requests\Admin\Students;

use App\Enums\RelationshipStatus;
use App\Models\User;
use App\Policies\StudentPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(StudentPolicy::class)->create($this->user());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'parents' => ['nullable', 'array'],
            'parents.*.name' => ['nullable', 'string', 'max:255', 'required_with:parents.*.email'],
            'parents.*.email' => ['nullable', 'string', 'email', 'max:255', 'distinct', 'required_with:parents.*.name'],
            'parents.*.phone' => ['nullable', 'string', 'max:30'],
            'parents.*.relationship' => ['nullable', 'string', Rule::enum(RelationshipStatus::class)],
        ];

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parents = $this->input('parents', []);
            if (! is_array($parents)) {
                return;
            }

            $studentEmail = strtolower(trim((string) $this->input('email')));
            $seenEmails = [];

            foreach ($parents as $index => $parent) {
                $pName = trim((string) ($parent['name'] ?? ''));
                $pEmail = strtolower(trim((string) ($parent['email'] ?? '')));

                if ($pName === '' && $pEmail === '') {
                    $validator->errors()->add(
                        "parents.{$index}.name",
                        'Parent row #'.($index + 1).' is empty. Please enter Parent Name and Parent Email, or remove this row.'
                    );

                    continue;
                }

                if ($pName === '') {
                    $validator->errors()->add(
                        "parents.{$index}.name",
                        'Parent row #'.($index + 1).': Parent Name is required.'
                    );
                }

                if ($pEmail === '') {
                    $validator->errors()->add(
                        "parents.{$index}.email",
                        'Parent row #'.($index + 1).': Parent Email is required.'
                    );
                }

                if ($pEmail !== '' && $pEmail === $studentEmail) {
                    $validator->errors()->add(
                        "parents.{$index}.email",
                        'Parent email cannot be the same as the student email.'
                    );
                }

                if ($pEmail !== '') {
                    if (in_array($pEmail, $seenEmails, true)) {
                        $validator->errors()->add(
                            "parents.{$index}.email",
                            'Duplicate parent email in this form. Each parent must have a unique email.'
                        );
                    }
                    $seenEmails[] = $pEmail;

                    // Parent email cannot belong to a non-parent existing user (e.g. teacher or admin)
                    $existingUser = User::where('email', $pEmail)->first();
                    if ($existingUser && ! $existingUser->hasRole('parent')) {
                        $validator->errors()->add(
                            "parents.{$index}.email",
                            "The email {$pEmail} is already registered as a {$existingUser->primaryRole()}."
                        );
                    }
                }
            }
        });
    }

    /**
     * @return array{name: string, email: string, password: string}
     */
    public function studentAttributes(): array
    {
        return $this->safe()->only(['name', 'email', 'password']);
    }

    /**
     * @return array<int, array{name: string, email: string, relationship: string}>
     */
    public function parentsData(): array
    {
        $parents = $this->input('parents', []);
        if (! is_array($parents)) {
            return [];
        }

        return array_values(array_filter($parents, fn ($p) => ! empty($p['name']) && ! empty($p['email'])));
    }
}
