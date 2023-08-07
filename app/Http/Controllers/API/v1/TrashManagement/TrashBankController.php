<?php

namespace App\Http\Controllers\API\v1\TrashManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrashBank;
use App\Traits\APIResponseTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TrashBankController extends Controller
{
    use APIResponseTrait;

    public function getBankSampah()
    {
        try {
            $trashBank = TrashBank::all();
            return $this->success("Success", $trashBank, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function chooseBankSampah(Request $request)
    {

        $user = User::findOrFail(Auth::user()->id);
        // dd(TrashBank::where('id', $request->trash_bank_id));
        if (is_null(TrashBank::where('id', $request->trash_bank_id)->first())) {
            return $this->error("Lokasi Bank sampah tidak ditemukan", 401);
        } else {
            try {
                $user->update([
                    'trash_bank_id' => $request->trash_bank_id
                ]);
            } catch (\Exception $e) {
                return $this->error("Failed", 401);
            }
            $trashBank = TrashBank::findOrFail($user->trash_bank_id);
            return $this->success("Success", $trashBank, 200);
        }
    }
}
