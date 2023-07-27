<?php

namespace App\Http\Controllers\API\v1\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use App\Models\TrashBank;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use APIResponseTrait;

    public function index()
    {
        try {
            if (isset(Auth::user()->trash_bank_id)) {
                $location = TrashBank::select('id', 'name', 'description')->findorFail(Auth::user()->trash_bank_id);
                return $this->success("success", $location, 200);
            } else{
                return $this->success("Anda Belum memilih lokasi bank sampah", NULL, 200);
            }
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
}
