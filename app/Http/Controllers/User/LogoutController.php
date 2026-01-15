<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return $this->success([], __('index.auth.logout'));
    }
}
