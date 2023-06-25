<?php

namespace App\Traits;

trait APIResponseTrait
{
    /**
     * Core of response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
     * @param   boolean         $isSuccess
     */
    public function coreResponse($message, $data = null, $statusCode, $token = null, $isSuccess = true)
    {
        // Check the params
        if(!$message) return response()->json(['message' => 'Message is required'], 500);

        $success = false;
        if($statusCode == '200' || $statusCode == '202'){
            $success = true;
        }

        // Send the response
        if($token != NULL){
            return response()->json([
                'success' => $success,
                'message' => $message,
                'token' => $token,
                'data' => $data
            ], $statusCode);
        }else{
            if($data == NULL){
                return response()->json([
                    'success' => $success,
                    'message' => $message
                ], $statusCode);
            }else{
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                    'data' => $data
                ], $statusCode);
            }
        }
    }

    /**
     * Send any success response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
     */
    public function success($message, $data, $statusCode = 200, $param1 = null)
    {
        return $this->coreResponse($message, $data, $statusCode, $param1);
    }

    /**
     * Send any error response
     *
     * @param   string          $message
     * @param   integer         $statusCode
     */
    public function error($message, $statusCode = 500)
    {
        return $this->coreResponse($message, null, $statusCode, false);
    }
}
