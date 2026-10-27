<?php

namespace App\Http\Requests\Admin\Roles;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'unique:roles,name'],
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
