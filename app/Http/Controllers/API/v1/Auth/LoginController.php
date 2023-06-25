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

    public function store(Request $request)
    {
        // if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = User::where('username', $request->username)->firstOrFail();
            $token = $user->createToken("IDEABOX")->plainTextToken;
            $data = array("id" => $user->id, "username" => $user->username);
            return $this->success("Success", $data, 200, $token);
        // }
    }
}
