<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\v1\Auth\OTPController;

class RegisterController extends Controller
{
    use APIResponseTrait;

    public function store(RegisterRequest $request)
    {
        // try {
           
            // dd($fileName);
            $user = $request->validated();
            $existingUsername = User::where('username', $request->username)->first();
            $existingPhone = User::where('phone', $request->phone)->first();
            if ($existingUsername != null) {
                return $this->error("Username telah terdaftar", 401);
            } else if ($existingPhone != null) {
                return $this->error("Nomor WA Telah Terdaftar", 401);
            } else {
                if(isset($request->profile_picture)){
                    $fileName = $request->profile_picture->hashName();
                    $files = Storage::disk('public')->put('profile_picture/', $request->profile_picture);
                }
                else{
                    $fileName = NULL;
                }
                
                DB::beginTransaction();
                $user = User::create([
                    'username' => $request->username,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'no_kk' => $request->no_kk,
                    'trash_bank_id' => $request->trash_bank_id,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'profile_picture' => $fileName
                ]);
                DB::commit();
                $otpController = new OTPController(); // Instantiate an object of OTPController
                $otpController->createOTP($user->id);
            }

            return $this->success("Registrasi Sukses, Silahkan verifikasi OTP", null, 200);
        // } catch (\Exception $e) {
        //     return $this->error("Failed", 401);
        // }
    }
}
