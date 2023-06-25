<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    use APIResponseTrait;

    public function store(Request $request)
    {
        try {
            $existingUsername = User::where('username', $request->username)->first();
            $existingEmail = User::where('email', $request->email)->first();

            if ($existingUsername != null) {
                return $this->error("Username telah terdaftar", 401);
            } else if ($existingEmail != null) {
                return $this->error("Email Telah Terdaftar", null, 401);
            } else {
                DB::beginTransaction();
                $user = User::create([
                    'username' => $request->username,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'trash_bank_id' => $request->trash_bank_id,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password)
                ]);
                DB::commit();
            }

            return $this->success("Success", null, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
}
