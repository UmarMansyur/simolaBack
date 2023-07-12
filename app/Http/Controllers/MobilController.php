<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Utils\HttpResponse;

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
      $exits = Mobil::all();
      if (!$exits) {
        return HttpResponse::not_found();
      }
      return HttpResponse::success($exits);
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
        'nama' => $request->nama,
        'kapasitas' => $request->kapasitas,
        'lokasi' => $request->lokasi,
        'deskripsi' => $request->deskripsi,
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
