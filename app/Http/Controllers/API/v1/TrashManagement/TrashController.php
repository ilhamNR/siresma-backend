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
use Carbon\Carbon;
use App\Http\Controllers\API\v1\Transaction\TransactionController;
use GrahamCampbell\ResultType\Success;
use App\Models\User;

use function PHPSTORM_META\map;

class TrashController extends Controller
{
    use APIResponseTrait;

    public function list()
    {
        try {
            $user = Auth::user()->id;
            $balance = new TransactionController();
            $balance = $balance->getBalance($user);
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
                    $item->trash_category = $item->trashCategory->category_name;
                    unset($item->trashCategory);
                }

                if ($item->status == "ON_PROCESS") {
                    $item->status = "Masih dalam Proses";
                } else if ($item->status == "DONE") {
                    $item->status = "Selesai";
                } else
                    unset($item->iot);
                unset($item->trashCategory);

                return $item;
            });
            // $data['user_balance'] = $balance;
            $finalData = [
                "user_balance" => $balance,
                "trash_store_logs" => $data->values(), // Reset array keys
            ];

            return $this->success('Success', $finalData, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function generateCode()
    {
        try {
            do {
                $code = Str::random(7);
                $generatedCode = Str::upper($code);
                $existingCode = IOT::where('code', $generatedCode)->first();
            } while (isset($existingCode));
            return $generatedCode;
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
    public function storeTrash(Request $request)
    {
        try {
            $user = Auth::user();
            if (is_null($user->trash_bank_id)) {
                return $this->error("Harap pilih bank sampah terlebih dahulu!", 404);
            }
            DB::beginTransaction();
            $data = GarbageSavingsData::create([
                'user_id' => Auth::user()->id,
                'trash_bank_id' => $request->trash_bank_id,
                'trash_category_id' => $request->trash_category_id,
                'store_date' => $request->store_date
            ]);
            DB::commit();
            $dataShow = GarbageSavingsData::where('id', $data->id)->get();
            $dataShow = $dataShow->map(function ($item) {
                // hide unshown column
                unset($item->user_id);
                // unset($item->trash_bank_id);
                unset($item->updated_at);
                unset($item->iot_id);
                unset($item->user_balance);
                unset($item->admin_balance);
                unset($item->status);
                unset($item->created_at);
                unset($item->id);

                return $item;
            });
            // dd($dataShow);
            return $this->success('Sukses menambahkan store sampah', $dataShow, 200);
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
        $redeemed_iot = NULL;
        $iot_data = IOT::where('code', $request->code)->first();
        $garbage_savings_data = GarbageSavingsData::findOrFail($request->garbage_savings_data_id);
        if (is_null($iot_data)) {
            return $this->error("Kode tidak valid", 404);
        }
        $redeemed_iot = GarbageSavingsData::where('iot_id', $iot_data->id)->first();

        if (isset($redeemed_iot)) {
            return $this->error("IOT sudah dihubungkan ke data sampah lain", 401);
        } else if (is_null($garbage_savings_data)) {
            return $this->error("Data stor sampah tidak valid", 404);
        } else if ($garbage_savings_data->user_id != Auth::user()->id) {
            return $this->error("Data Sampah ini bukan milik anda", 401);
        } else if (is_null(TrashCategory::where('id', $garbage_savings_data->trash_category_id)->first())) {
            return $this->error("Kategori sampah tidak valid", 404);
        } else {

            try {
                // calculate price
                $total_price = new TransactionController();
                $total_price = $total_price->calculatePrice($garbage_savings_data, $iot_data->weight);
                $admin_balance = $total_price * 30 / 100;
                $user_balance = $total_price * 70 / 100;

                DB::beginTransaction();
                $garbage_savings_data->update([
                    'iot_id' =>  $iot_data->id,
                    'user_balance' => $user_balance,
                    'admin_balance' => $admin_balance,
                    'status' => "DONE"
                ]);
                DB::commit();

                $create_transaction = new TransactionController();
                $create_transaction->createTransactionLog($user_balance, $garbage_savings_data->user_id, "STORE", $garbage_savings_data);
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
            return $this->error("Failed", 401);
        }
    }

}
