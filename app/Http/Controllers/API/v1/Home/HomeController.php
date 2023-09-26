<?php

namespace App\Http\Controllers\API\v1\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use App\Models\TrashBank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use APIResponseTrait;

    public function index()
{
    try {
        $user = Auth::user();

        // Define a cache key based on the user's ID
        $cacheKey = "user_location_{$user->id}";

        // Use Redis caching to store the location data for a specified duration (e.g., 60 minutes)
        $location = Cache::remember($cacheKey, 60, function () use ($user) {
            if (isset($user->trash_bank_id)) {
                return TrashBank::select('id', 'name', 'description')->findOrfail($user->trash_bank_id);
            }
            return null;
        });

        if ($location !== null) {
            return $this->success("Sukses mendapatkan data home", $location, 200);
        } else {
            return $this->success("Anda Belum memilih lokasi bank sampah", null, 200);
        }
    } catch (\Exception $e) {
        return $this->error("Failed", 401);
    }
}

}
