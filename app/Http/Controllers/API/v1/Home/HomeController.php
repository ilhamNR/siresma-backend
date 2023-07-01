<?php

namespace App\Http\Controllers\API\v1\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use App\Models\TrashBank;

class HomeController extends Controller
{
    use APIResponseTrait;

    public function index(Request $request)
    {
        // $location = $request->location;
        $location = TrashBank::findorFail($request->location);

        return $this->success("success", $location, 200);
    }
}