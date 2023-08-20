<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Provide your name',
            'email.required' => 'Email is required during registration',
            'email.email' => 'Provide a valid email address',
            'email.unique' => 'Email already registered',
            'password.required' => 'Provide your desired password',
            'password.confirmed' => 'Confirm your password'
        ];
    }
}
