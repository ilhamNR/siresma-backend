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
use App\Models\TrashCategory;
use App\Models\TransactionLog;

use function PHPSTORM_META\map;

class TrashController extends Controller
{
    use APIResponseTrait;

    public function list()
    {
        $user = Auth::user()->id;
        $data = GarbageSavingsData::where('user_id', $user)->with('trashCategory')->with('iot')->get();
        $data = $data->map(function ($item) {
            // hide iot id
            unset($item->iot_id);

            // hide iot timestamp
            if (isset($item->iot)) {
                unset($item->iot->created_at);
                unset($item->iot->updated_at);
            }


            //hide trash category_id
            unset($item->trash_category_id);

            // hide trash category timestamp
            if (isset($item->trashCategory)) {
                unset($item->trashCategory->created_at);
                unset($item->trashCategory->updated_at);
            }


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
            'trash_bank_id' => $request->trash_bank_id,
            'trash_category_id' => $request->trash_category_id,
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

    public function calculatePrice($garbage_savings_data, $weight)
    {
        $trash_category = TrashCategory::findOrFail($garbage_savings_data->trash_category_id);
        $total_price = $trash_category->price * $weight;

        return $total_price;
    }

    public function createTransactionLog($amount, $user_id, $type, $garbage_savings_data)
    {
        if ($type == "STORE") {
            DB::beginTransaction();
            TransactionLog::create([
                'code' => "STR0001",
                'type' => "STORE",
                'user_id' => $user_id,
                'amount' => $amount,
                'garbage_savings_data' => $garbage_savings_data

            ]);
        } else if ($type == "WITHDRAW") {
            DB::beginTransaction();
            TransactionLog::create([
                'code' => "WDR0001",
                'type' => "WITHDRAW",
                'user_id' => $user_id,
                'amount' => $amount,
            ]);
        }
    }
    public function connectIOT(Request $request)
    {
        $redeemed_iot = NULL;
        $iot_data = IOT::where('code', $request->code)->first();
        $garbage_savings_data = GarbageSavingsData::findOrFail($request->garbage_savings_data_id);
        if (is_null($iot_data)) {
            return $this->error("Kode tidak valid", 401);
        }
        $redeemed_iot = GarbageSavingsData::where('iot_id', $iot_data->id)->first();

        if (isset($redeemed_iot)) {
            return $this->error("IOT sudah dihubungkan ke data sampah lain", 401);
        } else if (is_null($garbage_savings_data)) {
            return $this->error("Data stor sampah tidak valid", 401);
        } else if ($garbage_savings_data->user_id != Auth::user()->id) {
            return $this->error("Data Sampah ini bukan milik anda", 401);
        } else {

            try {
                // calculate price
                $total_price = TrashController::calculatePrice($garbage_savings_data, $iot_data->weight);
                $admin_balance = $total_price*40/100;
                $user_balance = $total_price*60/100;

                DB::beginTransaction();
                $garbage_savings_data->update([
                    'iot_id' =>  $iot_data->id,
                    'user_balance' => $user_balance,
                    'admin_balance' => $admin_balance
                ]);
                DB::commit();
                return $this->success("Data IOT sudah terhubung", 200);
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

    public function getCategories()
    {
        try {
            $data = TrashCategory::get();
            $data = $data->map(function ($item) {
                unset($item->created_at);
                unset($item->updated_at);
                return $item;
            });

            return $this->success($data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed", 401);
        }
    }
}
