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
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use APIResponseTrait;

    public function store(RegisterRequest $request)
    {
        try {

            // dd($fileName);
            $user = $request->validated();
            $existingUsername = User::where('username', $request->username)->first();
            $existingPhone = User::where('phone', $request->phone)->first();
            if ($existingUsername != null) {
                return $this->error("Username telah terdaftar", 401);
            } else if ($existingPhone != null) {
                return $this->error("Nomor WA Telah Terdaftar", 401);
            } else {
                if (isset($request->profile_picture)) {
                    $fileName = $request->profile_picture->hashName();
                    $files = Storage::disk('public')->put('profile_picture/', $request->profile_picture);
                } else {
                    $fileName = NULL;
                }

                DB::beginTransaction();
                $user = User::create([
                    'username' => Str::lower($request->username),
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

            return $this->success("Registrasi Sukses, Silahkan verifikasi OTP", $user->id, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function changeNumber(Request $request)
    {
        $user = User::findOrfail($request->user_id);
        if (is_null($user)) {
            return $this->error("Akun tidak ditemukan", 404);
        } else if ($user->is_verified === 1) {
            return $this->error("Akun telah terverifikasi", 404);
        } else {
            try {
                DB::beginTransaction();
                $user->update([
                    'phone' => $request->phone
                ]);
                $otpController = new OTPController(); // Instantiate an object of OTPController
                $otpController->createOTP($user->id);
                DB::commit();
                return $this->success("Nomor berhasil dirubah, silahkan konfirmasi OTP dengan user baru", $user->id, 200);
            } catch (\Exception $e) {
                return $this->error("Failed", 401);
            }
        }
    }
}
