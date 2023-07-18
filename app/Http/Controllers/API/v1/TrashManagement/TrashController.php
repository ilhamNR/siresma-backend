<?php

namespace App\Http\Controllers\API\v1\TrashManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GarbageSavingsData;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\IOT;
use Illuminate\Support\Facades\DB;

class TrashController extends Controller
{
    use APIResponseTrait;

    public function list()
    {
        $user = Auth::user()->id;
        $data = GarbageSavingsData::where('user_id', $user)->with('iot')->get();
        $data = $data->map(function ($item) {
            // hide iot id
            unset($item->iot_id);

            return $item;
        });
        return $this->success('Success', $data, 200);
    }

    public function generateCode()
    {
        do {
            $code = Str::random(7);
            $generatedCode = Str::upper($code);
            $existingCode = IOT::where('code', $generatedCode)->first();
        } while (isset($existingCode));
        return $generatedCode;
    }
    public function storeTrash(Request $request)
    {
        try {
            DB::beginTransaction();
            GarbageSavingsData::create([
                'user_id' => Auth::user()->id,
                'balance' => 10000,
                'trash_category' => $request->trash_category,
                'store_date' => $request->store_date
            ]);
            DB::commit();
            return $this->success('Success', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }

    public function storeIOT(Request $request)
    {
        try {
            DB::beginTransaction();
            $iot = IOT::create([
                'weight' => $request->weight,
                'code' => TrashController::generateCode()
            ]);
            DB::commit();
            return $this->success($iot->code, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }

    public function connectIOT(Request $request)
    {
        $iot_data = IOT::where('code', $request->code)->first();
        $garbage_savings_data = GarbageSavingsData::where('id', $request->garbage_savings_data_id)->first();
        $redeemed_iot = GarbageSavingsData::where('iot_id', $iot_data->id)->first();
        if (is_null($iot_data)) {
            return $this->error("Kode tidak valid", 401);
        } else if (isset($redeemed_iot)) {
            return $this->error("IOT sudah dihubungkan ke data sampah lain", 401);
        } else if (is_null($garbage_savings_data)) {
            return $this->error("Data stor sampah tidak valid", 401);
        } else if ($garbage_savings_data->user_id != Auth::user()->id) {
            return $this->error("Data Sampah ini bukan milik anda", 401);
        } else {

            try {
                DB::beginTransaction();
                $garbage_savings_data->update([
                    'iot_id' =>  $iot_data->id
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error("Failed", 401);
            }
        }
    }

    public function updateWeight(Request $request)
    {
        $trash = GarbageSavingsData::findOrFail($request->garbage_savings_id);
        try {
            DB::beginTransaction();
            $trash->update([
                'weight' => $request->weight
            ]);
            DB::commit();
            return $this->success("Success", 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }
}
