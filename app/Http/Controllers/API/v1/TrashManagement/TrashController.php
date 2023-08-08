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
use GrahamCampbell\ResultType\Success;
use App\Models\User;

use function PHPSTORM_META\map;

class TrashController extends Controller
{
    use APIResponseTrait;

    public function list()
    {
        // try {
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
                    $item->trash_category = $item->trashCategory->category_name;
                    unset($item->trashCategory);
                }

                if ($item->status == "ON_PROCESS"){
                    $item->status = "Masih dalam Proses";
                } else if ($item->status == "DONE"){
                    $item->status = "Selesai";
                } else
                unset($item->iot);
                unset($item->trashCategory);

                return $item;
            });
            return $this->success('Success', $data, 200);
        // } catch (\Exception $e) {
        //     return $this->error("Failed", 401);
        // }
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
        // try {
            DB::beginTransaction();
            $data = GarbageSavingsData::create([
                'user_id' => Auth::user()->id,
                'trash_bank_id' => $request->trash_bank_id,
                'trash_category_id' => $request->trash_category_id,
                'store_date' => $request->store_date
            ]);
            DB::commit();
            $dataShow = GarbageSavingsData::where('id',$data->id)->get();
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
            return $this->success('Sukses menambahkan store sampah',$dataShow, 200);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return $this->error("Failed", 401);
        // }
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
        $trash_bank_id = User::where('id', $user_id)->first()->trash_bank_id;
        if ($type == "STORE") {
            $last_store_code = TransactionLog::where('code', 'like', "STR" . '%')->latest('code')->first();
            if (isset($last_store_code)) {
                $last_store_code_count = intval(substr($last_store_code->code, 3));
                $new_store_data_code = 'STR' . str_pad($last_store_code_count + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $new_store_data_code = 'STR0001';
            }
            try {
                DB::beginTransaction();
                TransactionLog::create([
                    'code' => $new_store_data_code,
                    'type' => "STORE",
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'garbage_savings_data_id' => $garbage_savings_data->id,
                    'trash_bank_id' => $trash_bank_id

                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error("Failed", 401);
            }
        } else if ($type == "WITHDRAW") {
            $last_withdraw_code = TransactionLog::where('code', 'like', "WDR" . '%')->latest('code')->first();
            if (isset($last_withdraw_code)) {
                $last_withdraw_code_count = intval(substr($last_withdraw_code->code, 3));
                $new_withdraw_data_code = 'WDR' . str_pad($last_withdraw_code_count + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $new_withdraw_data_code = 'WDR0001';
            }
            try {
                DB::beginTransaction();
                TransactionLog::create([
                    'code' => $new_withdraw_data_code,
                    'type' => "WITHDRAW",
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'garbage_savings_data_id' => NULL,
                    'trash_bank_id' =>  $trash_bank_id
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error("Failed", 401);
            }
        }
    }
    public function getBalance($user_id)
    {
        try {
            //get total of stored balance
            $store_data = TransactionLog::where('user_id', $user_id)->where('code', 'like', "STR" . '%')->get();
            $stored_balance = 0;
            foreach ($store_data as $storeLogs) {
                $stored_balance += $storeLogs->amount;
            }

            //get total of withdrawed balance
            $withdraw_data = TransactionLog::where('user_id', $user_id)->where('code', 'like', "WDR" . '%')->get();
            $withdrawed_balance = 0;
            foreach ($withdraw_data as $withdrawLogs) {
                $withdrawed_balance += $withdrawLogs->amount;
            }
            $balance = $stored_balance - $withdrawed_balance;

            return ($balance);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function withdraw(Request $request)
    {
        //get day count from last withdrawal
        $last_withdraw = TransactionLog::where('user_id', Auth::user()->id)->where('code', 'like', "WDR" . '%')->orderBy('created_at', 'desc')->first();
        //get current balance
        $balance = TrashController::getBalance(Auth::user()->id);
        if (isset($last_withdraw)) {
            $last_withdraw_date = Carbon::parse($last_withdraw->created_at);
            $todayDate = Carbon::today();
            $last_withdraw_interval = $last_withdraw_date->diffInDays($todayDate);

            if (30 > $last_withdraw_interval) {
                return $this->error("Permintaan tarik saldo anda terakhir masih kurang dari sebulan", 401);
            }
        }
        if ($balance >= $request->amount) {
            TrashController::createTransactionLog($request->amount, Auth::user()->id, "WITHDRAW", NULL);
        } else {
            return $this->error("Saldo Tidak Mencukupi", 401);
        }
        return $this->success("Permintaan Penarikan dana telah dibuat", 200);
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
        } else if (is_null(TrashCategory::where('id', $garbage_savings_data->trash_category_id)->first())) {
            return $this->error("Kategori sampah tidak valid", 401);
        } else {

            try {
                // calculate price
                $total_price = TrashController::calculatePrice($garbage_savings_data, $iot_data->weight);
                $admin_balance = $total_price * 40 / 100;
                $user_balance = $total_price * 60 / 100;

                DB::beginTransaction();
                $garbage_savings_data->update([
                    'iot_id' =>  $iot_data->id,
                    'user_balance' => $user_balance,
                    'admin_balance' => $admin_balance,
                    'status' => "DONE"
                ]);
                DB::commit();

                TrashController::createTransactionLog($user_balance, $garbage_savings_data->user_id, "STORE", $garbage_savings_data);
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
    public function getTransactionList()
    {
        try {
            $data = TransactionLog::where("user_id", Auth::user()->id)->get();
            return $this->success("Sukses mendapatkan data transaksi", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
}
