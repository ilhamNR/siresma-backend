<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\APIResponseTrait;

class LogoutController extends Controller
{
    use APIResponseTrait;

    public function logout()
    {
        try {
            Auth::user()->currentAccessToken()->delete();
            return $this->success("Logout sukses", null, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
}
