<?php

namespace App\Http\Requests\Admin\Roles;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = is_object($role) ? $role->id : $role;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
        ];
    }

    /**
     * @return array{name: string}
     */
    public function roleAttributes(): array
    {
        return [
            'name' => strtolower(trim((string) $this->input('name'))),
        ];
    }
}
