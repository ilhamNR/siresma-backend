<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OTP;
use Illuminate\Support\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Traits\APIResponseTrait;
use Illuminate\Support\Facades\DB;

class OTPController extends Controller
{
    use APIResponseTrait;
    public function sendOTP($code, $phone)
    {
        $phoneIndo = "62" . ltrim($phone, '0');
        $message_text = $code . " adalah kode untuk akun SIRESMA anda, berlaku hingga 2 menit ke depan";
        // The API endpoint URL
        $url = 'http://' . env('ONESENDER_SERVER_HOST') . ':' . env('ONESENDER_INSTANCE_PORT') . '/api/v1/messages';

        // JSON payload to send in the request
        $jsonData =
            '{
            "recipient_type": "individual",
            "to": "' . $phoneIndo . '",
            "type": "text",
            "text": {
               "body": "' . $message_text . '"
            },
            "date": "' . Carbon::now() . '",
            "tag": "random-tag",
            "unique": false
        }';
        // dd($jsonData);

        // API token
        $bearerToken = env('ONESENDER_API_KEY');

        try {
            // Send the API request with the JSON payload and API token
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Content-Type' => 'application/json',
            ])->post($url, json_decode($jsonData, true));


            // Check if the request was successful
            if ($response->successful()) {
                // Request was successful, handle the response if needed
                $responseData = $response->json();
                return response()->json($responseData); // Optional: Return the API response
            } else {
                // Request failed, handle the error if needed
                return response()->json(['error' => 'API request failed'], $response->status());
            }
        } catch (\Exception $e) {
            // Exception occurred, handle the error if needed
            return response()->json(['error' => $e->getMessage()], 500);
        }
        // dd($message_text);
    }

    public function createOTPExistingUser(Request $request)
    {
        $user_id = $request->user_id;
        $otpResult = OTPController::createOTP($user_id);
        return($otpResult);
    }
    public function createOTP($user_id)
    {
        $user = User::findOrFail($user_id);

        //get existing OTP
        $otpTimeDifference = 0;
        $existingOTP = OTP::where('user_id', $user_id)->orderBy('created_at', 'desc')->first();
        if(isset($existingOTP)){
            $otpCreationTime = Carbon::parse($existingOTP->created_at);
            $now = Carbon::now();
            $otpTimeDifference = $otpCreationTime->diffInSeconds($now);
            if ($otpTimeDifference < 120) {
                return $this->error("Anda baru saja meminta OTP, ulangi lagi dalam " . 120 - $otpTimeDifference . " detik", 401);
            }
        }
        if ($user->is_verified == 1) {
            return $this->error("Akun anda telah aktif", 401);
        }
        do {
            $code = sprintf("%06d", mt_rand(1, 999999));
            $existingCode = OTP::where('code', $code)->first();
        } while (isset($existingCode));
        DB::beginTransaction();
        OTP::create([
            'code' => $code,
            'user_id' => $user->id,
            'number' => $user->phone,
            'is_activated' => 0
        ]);
        OTPController::sendOTP($code, $user->phone);
        DB::commit();
    }

    public function verifyAccount(Request $request)
    {

        $otp = OTP::where('code', $request->otp_code)->first();
        $user = User::findOrFail($request->user_id)->first();
        $otpTimeDifference = 0;
        if (isset($otp)) {
            $otpCreationTime = Carbon::parse($otp->created_at);
            $now = Carbon::now();
            $otpTimeDifference = $otpCreationTime->diffInSeconds($now);
        };
        if (is_null($user)) {
            return $this->error("User tidak ditemukan", 401);
        } else if ($user->is_verified == 1) {
            return $this->error("User telah terverifikasi", 401);
        } else if (is_null($otp)) {
            return $this->error("OTP tidak valid", 401);
        } else if ($otp->user_id != $request->user_id) {
            return $this->error("OTP ini bukan untuk anda", 401);
        } else if ($otpTimeDifference > 120) {
            return $this->error("OTP telah kadaluarsa, silahkan meminta OTP kembali", 401);
        } else if ($otp->is_activated == 1) {
            return $this->error("OTP Telah Digunakan", 401);
        } else {
            DB::beginTransaction();
            $otp->update([
                "is_activated" => 1
            ]);
            $user->update([
                "is_verified" => 1
            ]);
            DB::commit();
        }
    }
}
