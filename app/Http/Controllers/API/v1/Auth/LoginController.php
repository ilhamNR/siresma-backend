<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use APIResponseTrait;

    public function store(Request $request, $id = null)
    {
        if(is_null(User::where('username', $request->username)->first())){
            return $this->error("Username tidak ditemukan", 401);
        }else if (User::where('username', $request->username)->first()->is_verified == 0) {
            $otpController = new OTPController(); // Instantiate an object of OTPController
            $otpController->createOTP(User::where('username', $request->username)->first()->id);
            return $this->success("Akun belum terverifikasi, silahkan verikasi OTP",User::where('username', $request->username)->first()->id,401);
        } else if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = User::where('username', $request->username)->firstOrFail();
            $token = $user->createToken("SIRESMA")->plainTextToken;
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
                "address" => $user->address,
                "no_kk" => $user->no_kk,
                "profile_picture" => $profile_picture
            );
            return $this->success("Success", $data, 200, $token);
        } else {
            return $this->error("Username atau password kamu salah", 401);
        }
    }
}
