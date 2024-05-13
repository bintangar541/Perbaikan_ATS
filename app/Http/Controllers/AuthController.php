<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiFormater;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' =>['login', 'logout']]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials))
        {
            return ApiFormater::sendResponse(400,'User not found', 'Silahkan cek kembali email dan password anda');
        }

        $respondWidthToken = [
            'acces_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ];
        return ApiFormater::sendResponse(200,'Logged-in', $respondWidthToken);
    }

    public function me()
    {
        return ApiFormater::sendResponse(200,'Succes',auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return ApiFormater::sendResponse(200,'Succes','Berhasil Logout');
    }
}