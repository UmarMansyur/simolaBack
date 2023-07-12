<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aula;
use App\Utils\HttpResponse;

class AulaController extends Controller
{
  public function __construct()
  {
    $this->rules = [
      'nama' => 'required|string',
      'kapasitas' => 'required|integer',
      'lokasi' => 'required|string',
      'deskripsi' => 'required|string',
      'status' => 'required|boolean'
    ];
    $this->middleware('auth', ['except' => ['index', 'show']]);
  }

  public function index()
  {
    try {
      $exits = Aula::all();
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
      $exist = Aula::where('nama', $request->nama)->first();
      if ($exist) {
        return HttpResponse::error('Aula already exist');
      }
      $response = Aula::create([
        'nama' => $request->nama,
        'kapasitas' => $request->kapasitas,
        'lokasi' => $request->lokasi,
        'deskripsi' => $request->deskripsi,
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
      $exist = Aula::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      $this->validate($request, $this->rules);
      $response = Aula::where('id', $id)->update([
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
      $exist = Aula::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      $response = Aula::where('id', $id)->delete();
      return HttpResponse::success($response, 'Data deleted');
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function show($id)
  {
    try {
      $exist = Aula::where('id', $id)->first();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }
}
