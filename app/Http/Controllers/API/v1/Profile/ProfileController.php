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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;

class ProfileController extends Controller
{
    use APIResponseTrait;
    public function details()
    {
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
        return $this->success("Sukses mendapatkan data profil user", $data, 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        if (isset($request->profile_picture)) {
            $fileName = $request->profile_picture->hashName();
            $files = Storage::disk('public')->put('profile_picture/', $request->profile_picture);
        }
        $user = User::findOrfail(Auth::user()->id);
        // dd($user->profile_picture);
        try {
            DB::beginTransaction();
            $user->update([
                // 'username' => Str::lower($request->username),
                'full_name' => $request->full_name ?? $user->full_name,
                // 'email' => $request->email,
                'address' => $request->address ?? $user->full_name,
                // 'no_kk' => $request->no_kk,
                'phone' => $request->phone ?? $user->full_name,
                // 'profile_picture' => $fileName
            ]);
            if (isset($request->profile_picture)) {
                $user->update([
                    'profile_picture' => $fileName
                ]);
            }
            DB::commit();
            return $this->success("Profile berhasil diubah", 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = User::findOrfail(Auth::user()->id);
        if (isset($request->new_password)) {
            try {
                DB::beginTransaction();
                $user->update([
                    'password' => Hash::make($request->new_password)
                ]);
                DB::commit();
                return $this->success("Password berhasil dirubah", 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error("Failed", 401);
            }
        }
    }
}
