<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can("users.create") ||
            $this->user()->can("users.edit");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route("user") ? $this->route("user")->id : null;
        $isUpdating = $this->isMethod("PUT") || $this->isMethod("PATCH");

        return [
            "name" => "required|string|max:255",
            "email" => [
                "required",
                "email",
                "max:255",
                Rule::unique("users")->ignore($userId),
            ],
            "password" => [
                $isUpdating ? "nullable" : "required",
                "string",
                "min:8",
                "confirmed",
            ],
            "roles" => "nullable|array",
            "roles.*" => "exists:roles,name",
            "status" => "sometimes|required|in:active,inactive",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            "name.required" => "The name field is required.",
            "name.string" => "The name must be a string.",
            "name.max" => "The name may not be greater than 255 characters.",

            "email.required" => "The email field is required.",
            "email.email" => "The email must be a valid email address.",
            "email.unique" => "This email address is already taken.",
            "email.max" => "The email may not be greater than 255 characters.",

            "password.required" => "The password field is required.",
            "password.string" => "The password must be a string.",
            "password.min" => "The password must be at least 8 characters.",
            "password.confirmed" => "The password confirmation does not match.",

            "roles.array" => "The roles field must be an array.",
            "roles.*.exists" => "The selected role is invalid.",

            "status.required" => "The status field is required.",
            "status.in" => "The selected status is invalid.",
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            "name" => "full name",
            "email" => "email address",
            "password" => "password",
            "password_confirmation" => "password confirmation",
            "roles" => "roles",
            "status" => "account status",
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic can be added here

            // Prevent removing admin role from the last admin user
            if ($this->isMethod("PUT") || $this->isMethod("PATCH")) {
                $user = $this->route("user");
                $requestedRoles = $this->input("roles", []);

                if (
                    $user &&
                    $user->hasRole("admin") &&
                    !in_array("admin", $requestedRoles)
                ) {
                    $adminCount = \App\Models\User::query()
                        ->whereHas("roles", function ($q) {
                            $q->where("name", "admin");
                        })
                        ->count();

                    if ($adminCount <= 1) {
                        $validator
                            ->errors()
                            ->add(
                                "roles",
                                "Cannot remove admin role from the last admin user.",
                            );
                    }
                }
            }
        });
    }
}
