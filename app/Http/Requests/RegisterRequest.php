<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

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
            'username' => request()->route('auth/registration') ? 'required|string|regex:/^\S*$/u|max:255|unique:users,username'. request()->route('auth/registration')
            : 'required|string|regex:/^\S*$/u|max:255|unique:users,username',
            'full_name' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'phone' => request()->route('auth/registration') ? 'required|min:10|max:13|unique:users,phone'. request()->route('auth/registration')
            : 'required||min:10|max:13|unique:users,phone',
            'no_kk' => request()->route('auth/registration') ? 'required|min:16|max:16|unique:users,no_kk'. request()->route('auth/registration')
            : 'required|min:16|max:16|unique:users,no_kk',
            'password' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255|min:8',
            'address' => request()->route('auth/registration') ? 'required|max:255'. request()->route('auth/registration')
            : 'required|max:255',
            'profile_picture' =>  request()->route('auth/registration') ? 'nullable'. request()->route('auth/registration') : File::image()->max(5120) ,
        ];     
    }
}
