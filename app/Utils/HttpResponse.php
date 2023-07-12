<?php
namespace App\Utils;
class HttpResponse
{

  public static function created($data)
  {
    return response()->json([
      'status' => 'success',
      'message' => 'Data created',
      'data' => $data
    ], 201);
  }

  public static function success($data, $message = 'Data found')
  {
    return response()->json([
      'status' => 'success',
      'message' => $message,
      'data' => $data
    ], 200);
  }

  public static function error($message, $error_code = 500)
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
      'data' => []
    ], $error_code);
  }

  public static function not_found()
  {
    return response()->json([
      'status' => 'error',
      'message' => 'Data not found',
      'data' => []
    ], 404);
  }
}
