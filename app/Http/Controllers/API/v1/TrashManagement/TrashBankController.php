<?php

namespace App\Http\Controllers\API\v1\TrashManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrashBank;
use App\Traits\APIResponseTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrashBankController extends Controller
{
    use APIResponseTrait;

    public function getBankSampah()
    {
        try {
            // Define a cache key for the trash bank data
            $cacheKey = 'trash_bank_data';

            // Use Redis caching to store the trash bank data for a specified duration (e.g., 60 minutes)
            $trashBank = Cache::remember($cacheKey, 60, function () {
                // Retrieve the trash bank data from the database only if it's not already cached
                return TrashBank::all();
            });

            return $this->success("Sukses mendapatkan daftar bank sampah", $trashBank, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function chooseBankSampah(Request $request)
{
    $user = User::findOrFail(Auth::user()->id);

    if (is_null(TrashBank::where('id', $request->trash_bank_id)->first())) {
        return $this->error("Lokasi Bank sampah tidak ditemukan", 404);
    } else {
        try {
            DB::beginTransaction();

            // Update the user's trash_bank_id in the database
            $user->update([
                'trash_bank_id' => $request->trash_bank_id
            ]);

            // Set the location data in the cache
            $location = TrashBank::findOrFail($user->trash_bank_id);
            $cacheKey = "user_location_{$user->id}";
            Cache::put($cacheKey, $location, now()->addHours(1)); // Adjust the cache duration as needed

            DB::commit();

            return $this->success("Sukses memilih bank sampah", $location->id, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }
}

}
