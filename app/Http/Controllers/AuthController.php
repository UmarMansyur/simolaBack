<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\HttpResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{

  public function __construct()
  {
    $this->middleware('auth', ['except' => ['login']]);
  }

  public function login() {
    try {
        $credentials = request()->only(['username', 'password']);
        if (!$token =Auth::attempt($credentials)) {
          return HttpResponse::error('Unauthorized', 401);
        }
        return $this->respondWithToken($token);
    } catch (\Throwable $th) {
        return HttpResponse::error($th->getMessage());
    }
  }

  public function getMe() {
    return response()->json(auth()->user());
  }

  public function logout() {
    auth()->logout();
    return HttpResponse::success([], 'Logout success');
  }

  public function refresh() {
    return $this->respondWithToken(auth()->refresh());
  }

  protected function respondWithToken($token) {
    return HttpResponse::success([
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() . " Menit",
      'access_token' => $token,
    ], 'Login success');
  }



}