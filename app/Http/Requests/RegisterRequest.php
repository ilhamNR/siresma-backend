<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'full_name' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'phone' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'no_kk' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'password' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'address' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255'
        ];
    }
}
