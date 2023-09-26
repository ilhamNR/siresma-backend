<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\v1\Transaction\TransactionController;

class ProcessIOTData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $garbageSavingsData;
    protected $iotData;

    public function __construct($garbageSavingsData, $iotData)
    {
        $this->garbageSavingsData = $garbageSavingsData;
        $this->iotData = $iotData;
    }

    public function handle()
    {
        try {
            // Calculate price
            $totalPrice = (new TransactionController())->calculatePrice($this->garbageSavingsData, $this->iotData->weight);
            $adminBalance = $totalPrice * 40 / 100;
            $userBalance = $totalPrice * 60 / 100;

            DB::beginTransaction();
            $this->garbageSavingsData->update([
                'iot_id' => $this->iotData->id,
                'user_balance' => $userBalance,
                'admin_balance' => $adminBalance,
                'status' => "DONE"
            ]);
            DB::commit();

            (new TransactionController())->createTransactionLog($userBalance, $this->garbageSavingsData->user_id, "STORE", $this->garbageSavingsData);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Rethrow the exception to let Laravel handle failed jobs
        }
    }
}
