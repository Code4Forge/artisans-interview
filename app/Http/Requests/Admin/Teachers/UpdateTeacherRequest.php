<?php

namespace App\Http\Requests\Admin\Teachers;

use App\Models\User;
use App\Policies\TeacherPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $teacher */
        $teacher = $this->route('teacher');

        return app(TeacherPolicy::class)->update($this->user(), $teacher);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $teacher */
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($teacher->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array{name: string, email: string, password?: string|null}
     */
    public function teacherAttributes(): array
    {
        return $this->safe()->only(['name', 'email', 'password']);
    }
}
