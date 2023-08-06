<?php

namespace App\Http\Controllers\API\v1\Profile;

use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\TrashBank;

class ProfileController extends Controller
{
    use APIResponseTrait;
    public function details(){
        $user = User::findOrfail(Auth::user()->id);
        if (isset($user->trash_bank_id)) {
            $location = TrashBank::findOrfail($user->trash_bank_id)->name;
        } else {
            $location = "";
        }
        if ($user->profile_picture == ("" or NULL)) {
            $profile_picture = asset('NULLpp.png');
        } else {
            $profile_picture = asset('storage/profile_picture/' . $user->profile_picture);
        }
        $data = array(
            "id" => $user->id,
            "full_name" => $user->full_name,
            "role" => $user->role,
            "phone" => $user->phone,
            "location" => $location,
            "address" => $user->address,
            "no_kk" => $user->no_kk,
            "profile_picture" => $profile_picture
        );
        return $this->success("Success", $data, 200);
    }

    public function updateProfile(Request $request){
        $user = User::findOrfail(Auth::user()->id);
        dd($user->profile_picture);
        $user->update([
            // 'username' => Str::lower($request->username),
            'full_name' => $request->full_name,
            // 'email' => $request->email,
            'address' => $request->address,
            // 'no_kk' => $request->no_kk,
            'phone' => $request->phone,
            // 'profile_picture' => $fileName
        ]);
    }
}
