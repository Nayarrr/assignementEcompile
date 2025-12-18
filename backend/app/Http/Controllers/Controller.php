<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function apiResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => $code < 400,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
