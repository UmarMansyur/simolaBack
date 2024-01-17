<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aula;
use App\Utils\HttpResponse;
use App\Utils\Pagination;

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
      $request = request()->all();
      $request = request()->all();
      $limit = $request['limit'] ?? 10;
      $page = $request['page'] ?? 1;
      $offset = intval($page - 1) * intval($limit);
      
      $exist = Aula::offset($offset)->limit($limit)->get();
      $totalRows = Aula::count();
      if(!$exist) {
        return HttpResponse::not_found();
      }

     if(!empty($request['search'])) {
        $exist = Aula::where('merk', 'like', '%'.$request['search'].'%')
        ->orWhere('nama', 'like', '%'.$request['search'].'%')
        ->orWhere('kapasitas', 'like', '%'.$request['search'].'%')
        ->orWhere('lokasi', 'like', '%'.$request['search'].'%')
        ->orWhere('deskripsi', 'like', '%'.$request['search'].'%')
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
      return HttpResponse::error($th->getMessage());
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
