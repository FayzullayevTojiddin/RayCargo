<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function success($data, $message)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], 202);
    }

    public function error($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 422);
    }

    public function response($data)
    {
        return response()->json([
            'data' => $data
        ], 200);
    }
}
