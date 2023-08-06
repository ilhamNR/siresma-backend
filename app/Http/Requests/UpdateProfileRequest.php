<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateProfileRequest extends FormRequest
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
            'full_name' => request()->route('auth/registration') ? 'required|max:255' . request()->route('auth/registration')
                : 'required|max:255',
            'phone' => request()->route('auth/registration') ? 'required|min:10|max:13|unique:users,phone' . request()->route('auth/registration')
                : 'required||min:10|max:13|unique:users,phone',
            'address' => request()->route('auth/registration') ? 'required|max:255' . request()->route('auth/registration')
                : 'required|max:255',
            'profile_picture' =>  request()->route('auth/registration') ? 'nullable' . request()->route('auth/registration') : File::image()->max(5120),
        ];
    }
}
