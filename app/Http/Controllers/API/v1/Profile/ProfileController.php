<?php

namespace App\Http\Controllers\API\v1\Profile;

use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function updateProfile(Request $request){
        $user = User::findOrfail(Auth::user()->id);
        $user->update([
            'username' => Str::lower($request->username),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'address' => $request->address,
            'no_kk' => $request->no_kk,
            'phone' => $request->phone,
            'profile_picture' => $fileName
        ]);
    }
}
