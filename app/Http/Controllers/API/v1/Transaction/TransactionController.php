<?php

namespace App\Http\Controllers\API\v1\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrashCategory;
use App\Models\User;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\v1\TrashManagement\TrashController;
use Illuminate\Support\Carbon;
use App\Traits\APIResponseTrait;

class TransactionController extends Controller
{
    use APIResponseTrait;
    public function getTransactionList(Request $request)
    {
        try {
            $user = Auth::user();
            $data = TransactionLog::where("user_id", $user->id)->get();
            if (isset($data)) {
                //get oldest data
                $oldestData = collect($data)->sortBy('created_at')->first();

                //get newest data
                $newestData = collect($data)->sortByDesc('created_at')->first();
                // dd($newestData);
                if (isset($request->month_filter)) {
                    $monthFilter = Carbon::createFromFormat('m-Y', $request->month_filter);

                    // Filter the collection based on the specified month and year
                    $data = collect($data)->filter(function ($item) use ($monthFilter) {
                        $createdAt = Carbon::parse($item['created_at']);
                        return $createdAt->month == $monthFilter->month && $createdAt->year == $monthFilter->year;
                    });
                }
                $balance = new TransactionController();
                $balance = $balance->getBalance($user->id);
                $finalData = [
                    "user_balance" => $balance,
                    "oldest_data_month" => Carbon::parse($oldestData->created_at)->format('m-Y'),
                    "newest_data_month" => Carbon::parse($newestData->created_at)->format('m-Y'),
                    "transaction_list" => $data->values(), // Reset array keys

                ];
                return $this->success("Sukses mendapatkan data transaksi", $finalData, 200);
            } else {
                return $this->error("Data transaksi masih kosong", 404);
            }
        } catch (\Exception $e) {
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
        $balance = TransactionController::getBalance(Auth::user()->id);
        if (isset($last_withdraw)) {
            $last_withdraw_date = Carbon::parse($last_withdraw->created_at);
            $todayDate = Carbon::today();
            $last_withdraw_interval = $last_withdraw_date->diffInDays($todayDate);

            if (30 > $last_withdraw_interval) {
                return $this->error("Permintaan tarik saldo anda terakhir masih kurang dari sebulan", 401);
            }
        }
        if ($balance >= $request->amount) {
            TransactionController::createTransactionLog($request->amount, Auth::user()->id, "WITHDRAW", NULL);
        } else {
            return $this->error("Saldo Tidak Mencukupi", 401);
        }
        return $this->success("Permintaan Penarikan dana telah dibuat", 200);
    }
}
