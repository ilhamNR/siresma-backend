<?php

namespace App\Http\Controllers\API\v1\TrashManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GarbageSavingsData;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\IOT;


class TrashController extends Controller
{
    use APIResponseTrait;

    public function storeTrash(Request $request)
    {
        $code = Str::random(7);
        $generatedCode = Str::upper($code);

        GarbageSavingsData::create([
            'user_id' => 1,
            'balance' => 10000,
            'trash_category' => $request->trash_category,
            'generated_code' => $generatedCode,
            'store_date' => $request->store_date
        ]);

        return $this->success('Success',200);
    }

    public function connectIOT(Request $request){
        $trashStore = GarbageSavingsData::where('generated_code', $request->code)->first();
        // dd($trashStore->id);
        IOT::create([
             'weight' => $request->weight,
             'garbage_savings_data_id' => $trashStore->id
        ]);

        $trashStore->update([
            'weight' => $request->weight
        ]);

        return $this->success($trashStore,200);
    }
}
