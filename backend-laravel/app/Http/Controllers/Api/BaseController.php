<?php

namespace App\Http\Controllers\Api;

class BaseController
{

    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }
    public function sendError($error, $code = 500)
    {
        $response = [
            'status' => false,
            'message' => $error,
        ];
        return response()->json($response, $code);
    }

}
