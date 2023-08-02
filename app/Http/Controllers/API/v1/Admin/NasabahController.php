<?php

namespace App\Http\Controllers\API\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\APIResponseTrait;
use App\Models\TransactionLog;
use App\Models\GarbageSavingsData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NasabahController extends Controller
{
    use APIResponseTrait;
    public function getAllUsers()
    {
        try {
            $data = User::where('role', 'nasabah')->get();
            $data = $data->map(function ($item) {
                // hide unshown column
                unset($item->username);
                unset($item->email);
                unset($item->address);
                unset($item->no_kk);
                unset($item->trash_bank_id);
                unset($item->role);
                unset($item->is_verified);
                unset($item->created_at);
                unset($item->updated_at);

                if (is_null($item->profile_picture)) {
                    $item->profile_picture = asset('NULLpp.png');
                }
                return $item;
            });
            return $this->success("Success", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
    public function getNasabahDetails(Request $request)
    {
        try {
            $data = User::findOrFail($request->user_id)->get();
            $data = $data->map(function ($item) {
                // hide unshown column
                unset($item->username);
                unset($item->email);
                unset($item->trash_bank_id);
                unset($item->role);
                unset($item->is_verified);
                unset($item->created_at);
                unset($item->updated_at);

                if (is_null($item->profile_picture)) {
                    $item->profile_picture = asset('NULLpp.png');
                }
                return $item;
            });
            return $this->success("Success", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function getTransactionDetails(Request $request)
    {
        try {
            $transaction_data = GarbageSavingsData::where("user_id", $request->user_id)->get();
            // dd($transaction_data);

            // get user balance
            $totalUserBalance = 0;
            foreach ($transaction_data as $item) {
                if (isset($item['user_balance']) && is_numeric($item['user_balance'])) {
                    $totalUserBalance += $item['user_balance'];
                }
            }
            // get user balance
            $totalAdminBalance = 0;
            foreach ($transaction_data as $item) {
                if (isset($item['admin_balance']) && is_numeric($item['admin_balance'])) {
                    $totalAdminBalance += $item['admin_balance'];
                }
            }

            $data = TransactionLog::where('user_id', $request->user_id)->get();
            $data['total_income'] = $totalAdminBalance + $totalUserBalance;
            $data['user_income'] = $totalUserBalance;
            $data['admin_income'] = $totalAdminBalance;
            $data = $data->map(
                function ($item) {

                    // include iot data
                    if (isset($item->garbageSavingsData->iot_id)) {
                        $item->weight = $item->garbageSavingsData->iot->weight;
                        unset($item->garbageSavingsData->iot);
                    }

                    // hide unshown column
                    unset($item->type);
                    unset($item->user_id);
                    unset($item->is_approved);
                    unset($item->garbage_savings_data_id);
                    unset($item->garbageSavingsData);
                    return $item;
                }
            );

            return $this->success("Success", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function getIncomingTransactions()
    {
        try {
            // $trash_bank_admin_id = ;
            // dd($trash_bank_admin_id);
            $data = TransactionLog::where('type', 'STORE')->where('trash_bank_id', Auth::user()->trash_bank_id)->with('garbageSavingsData')->get();
            // $data = TransactionLog::where('type', 'STORE')->with('garbageSavingsData')->get();


            // $data = $data->toArray();

            // $data = array_filter($data, function ($item) {
            //     return isset($item['garbage_savings_data']['trash_bank_id']) && $item['garbage_savings_data']['trash_bank_id'] === Auth::user()->trash_bank_id;
            // });
            // $data = collect($data);
            $data = $data->map(
                function ($item) {
                    // hide unshown column
                    $item['name'] = User::findOrFail($item['user_id'])->first()->full_name;
                    $item['store_date'] = $item->garbageSavingsData->store_date;
                    unset($item['type']);
                    unset($item['trash_bank_id']);
                    unset($item['user_id']);
                    unset($item['is_approved']);
                    unset($item['garbage_savings_data_id']);
                    unset($item->garbageSavingsData);
                    unset($item['updated_at']);
                    unset($item['created_at']);
                    return $item;
                }
            );
            return $this->success("Success", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }

    public function getOutcomingTransactions()
    {
        try {
            $data = TransactionLog::where('type', 'WITHDRAW')->where('trash_bank_id', Auth::user()->trash_bank_id)->with('garbageSavingsData')->get();
            $data = $data->map(
                function ($item) {
                    // hide unshown column
                    $item['name'] = User::findOrFail($item['user_id'])->first()->full_name;
                    unset($item['type']);
                    unset($item['garbage_savings_data_id']);
                    unset($item['user_id']);
                    unset($item['garbageSavingsData']);
                    unset($item['trash_bank_id']);
                    unset($item['updated_at']);
                    unset($item['created_at']);
                    return $item;
                }
            );
            $data = $data->toArray();
            return $this->success("Success", $data, 200);
        } catch (\Exception $e) {
            return $this->error("Failed", 401);
        }
    }
    public function approveWithdrawal(Request $request)
    {
        $transaction = TransactionLog::findOrFail($request->transaction_id);
        // $applicant = User::findOrFail($transaction->user_id);
        $pengelola_location = Auth::user()->trash_bank_id;

        if ($transaction->trash_bank_id != $pengelola_location) {
            return $this->error("Lokasi bank sampah anda dan pengajuan tidak sesuai", 401);
        } else if ($transaction->is_approved === 1) {
            return $this->error("Data ini sebelumnya telah disetujui", 401);
        } else {
            try {
                DB::beginTransaction();
                $transaction->update([
                    'is_approved' => 1
                ]);
                DB::commit();
                return $this->success("Pencairan telah disetujui", 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error("Failed", 401);
            }
        }
    }
}
