<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Utils\HttpResponse;
use App\Utils\Pagination;

class MobilController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth', ['except' => ['index', 'show']]);
    $this->rules = [
      'merk' => 'required|string',
      'plat_nomer' => 'required',
      'kapasitas' => 'required|integer',
      'warna' => 'required|string',
      'status' => 'required|boolean'
    ];
  }

  public function index()
  {
    try {
      $request = request()->all();
      $request = request()->all();
      $limit = $request['limit'] ?? 10;
      $page = $request['page'] ?? 1;
      $offset = intval($page - 1) * intval($limit);
      
      $exist = Mobil::offset($offset)->limit($limit)->get();
      $totalRows = Mobil::count();
      if(!$exist) {
        return HttpResponse::not_found();
      }

     if(!empty($request['search'])) {
        $exist = Mobil::where('merk', 'like', '%'.$request['search'].'%')
        ->orWhere('plat_nomer', 'like', '%'.$request['search'].'%')
        ->orWhere('kapasitas', 'like', '%'.$request['search'].'%')
        ->orWhere('warna', 'like', '%'.$request['search'].'%')
        ->orWhere('status', 'like', '%'.$request['search'].'%')
        ->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count();
      }
      $totalPage = ceil($totalRows / intval($limit));
      $result = [
        'page' => intval($page),
        'limit' => intval($limit),
        'total_page' => $totalPage,
        'total_rows' => $totalRows,
        'data' => $exist
      ];
      return HttpResponse::success($result);
    } catch (\Throwable $th) {
      return HttpResponse::not_found($th->getMessage());
    }
  }

  public function store(Request $request)
  {
    try {
      $this->validate($request, $this->rules);
      $response = Mobil::create([
        'merk' => $request->merk,
        'plat_nomer' => $request->plat_nomer,
        'kapasitas' => $request->kapasitas,
        'warna' => $request->warna,
        'status' => $request->status
      ]);
      return HttpResponse::created($response);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $exist = Mobil::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      $this->validate($request, $this->rules);
      $response = Mobil::where('id', $id)->update([
        'merk' => $request->merk,
        'plat_nomer' => $request->plat_nomer,
        'kapasitas' => $request->kapasitas,
        'warna' => $request->warna,
        'status' => $request->status
      ]);
      return HttpResponse::success($response, 'Data updated');
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function destroy($id)
  {
    try {
      $exist = Mobil::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      $response = Mobil::where('id', $id)->delete();
      return HttpResponse::success($response, 'Data deleted');
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function show($id)
  {
    try {
      $exist = Mobil::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }
}
