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
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    use APIResponseTrait;
    public function details()
{
    try {
        // Define a cache key based on the user's ID
        $user = Auth::user();
        $cacheKey = "user_details_{$user->id}";

        // Use Redis caching to store the user details for a specified duration (e.g., 60 minutes)
        $data = Cache::remember($cacheKey, 60, function () use ($user) {
            $location = "";
            if (isset($user->trash_bank_id)) {
                $location = TrashBank::findOrfail($user->trash_bank_id)->name;
            }

            $profile_picture = "";
            if ($user->profile_picture == ("" or NULL)) {
                $profile_picture = asset('NULLpp.png');
            } else {
                $profile_picture = asset('storage/profile_picture/' . $user->profile_picture);
            }

            return [
                "id" => $user->id,
                "full_name" => $user->full_name,
                "role" => $user->role,
                "phone" => $user->phone,
                "location" => $location,
                "address" => $user->address,
                "no_kk" => $user->no_kk,
                "profile_picture" => $profile_picture,
            ];
        });

        return $this->success("Sukses mendapatkan data profil user", $data, 200);
    } catch (\Exception $e) {
        return $this->error("Failed", 401);
    }
}


    public function updateProfile(UpdateProfileRequest $request)
{
    $user = User::findOrfail(Auth::user()->id);

    try {
        DB::beginTransaction();

        $profileData = Cache::get("user_details_{$user->id}");

        if (isset($request->profile_picture)) {
            $fileName = $request->profile_picture->hashName();
            $files = Storage::disk('public')->put('profile_picture/', $request->profile_picture);

            // Update the profile data with the new profile picture
            $profileData['profile_picture'] = $fileName;
        }

        $updatedData = [
            'full_name' => $request->filled('full_name') ? $request->full_name : $user->full_name,
            'address' => $request->filled('address') ? $request->address : $user->address,
            'phone' => $request->filled('phone') ? $request->phone : $user->phone,
            'profile_picture' => $profileData['profile_picture']
        ];

        // Update the user model with the new data
        $user->update($updatedData);

        // Update the cache with the updated profile data
        Cache::put("user_details_{$user->id}", $profileData, now()->addHours(1)); // Adjust the cache duration as needed

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
