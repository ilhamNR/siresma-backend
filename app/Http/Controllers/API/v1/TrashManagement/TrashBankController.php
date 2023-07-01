<?php

namespace App\Http\Controllers\API\v1\TrashManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrashBank;
use App\Traits\APIResponseTrait;

class TrashBankController extends Controller
{
    use APIResponseTrait;
    public function getBankSampah(){
        $trashBank = TrashBank::all();
        return $this->success("Success",$trashBank, 200);
    }
}
