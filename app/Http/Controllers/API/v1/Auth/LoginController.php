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
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = User::where('username', $request->username)->firstOrFail();
            $token = $user->createToken("SIRESMA")->plainTextToken;
            $data = array("id" => $user->id, "full_name" => $user->full_name, "phone" => $user->phone, "email" => $user->email);
            return $this->success("Success", $data, 200, $token);
        }else{
            return $this->error("Username atau password kamu salah", 401);
        }
    }
}
