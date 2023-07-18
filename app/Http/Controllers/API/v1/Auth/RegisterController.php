<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    use APIResponseTrait;

    public function store(RegisterRequest $request)
    {
        try {
            $user = $request->validated();
            $existingUsername = User::where('username', $request->username)->first();
            $existingEmail = User::where('email', $request->email)->first();

            if ($existingUsername != null) {
                return $this->error("Username telah terdaftar", 401);
            } else if ($existingEmail != null) {
                return $this->error("Email Telah Terdaftar", 401);
            } else {
                DB::beginTransaction();
                $user = User::create([
                    'username' => $request->username,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'no_kk' => $request->no_kk,
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
