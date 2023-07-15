<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\HttpResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth', ['except' => ['login']]);
  }

  public function login()
  {
    try {
      $credentials = request()->only(['username', 'password']);
      if (!$token = Auth::attempt($credentials)) {
        return $this->loginToSimat($credentials);
      }
      return $this->respondWithToken($token);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function getMe()
  {
    return response()->json(auth()->user());
  }

  public function logout()
  {
    auth()->logout();
    return HttpResponse::success([], 'Logout success');
  }

  public function refresh()
  {
    return $this->respondWithToken(auth()->refresh());
  }

  protected function respondWithToken($token, $isSimat = false)
  {
    return HttpResponse::success([
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() . " minutes",
      'is_simat' => $isSimat,
      'access_token' => $token
    ], 'Login success');
  }

  protected function loginToSimat($credentials)
  {
    try {
      $data = [
        'username' => $credentials['username'],
        'password' => $credentials['password'],
      ];
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, 'https://api.unira.ac.id/v1/token');
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      if ($httpCode == 201) {
        $token = json_decode($response)->data->attributes->access;
        return $this->respondWithToken($token, true);
      }
      return HttpResponse::error('Unauthorized', 401);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }
}
