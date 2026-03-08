<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $company = $this->header('company');

        $uniqueRule = $company !== null
            ? Rule::unique('roles')->where('team_id', $company)
            : Rule::unique('roles')->whereNull('team_id');

        $rules = [
            'name' => [
                'required',
                'string',
                $uniqueRule,
            ],
            'abilities' => [
                'required',
            ],
            'abilities.*' => [
                'required',
            ],
        ];

        if (in_array($this->getMethod(), ['PUT', 'PATCH'])) {
            $uniquePutRule = $company !== null
                ? Rule::unique('roles')->ignore($this->route('role')->id, 'id')->where('team_id', $company)
                : Rule::unique('roles')->ignore($this->route('role')->id, 'id')->whereNull('team_id');

            $rules['name'] = [
                'required',
                'string',
                $uniquePutRule,
            ];
        }

        return $rules;
    }

    public function getRolePayload(): array
    {
        $company = $this->header('company');

        return [
            'name' => $this->validated('name'),
            'team_id' => $company !== null ? (int) $company : null,
            'guard_name' => 'web',
        ];
    }
}
