<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Utils\HttpResponse;
use Illuminate\Http\Request;

class PenyewaanController extends Controller
{
  public function __construct()
  {
    $this->rules = [
      'tanggal_pengajuan' => 'date',
      'penanggung_jawab' => 'required|string',
      'asal_surat' => 'required|string',
      'jenis_surat' => 'required|string',
      'type' => 'required|string',
      'tanggal_mulai' => 'required|date',
      'tanggal_selesai' => 'required|date',
      'lampiran' => 'file|mimes:pdf,doc,docx'
    ];
    $this->middleware('auth', ['except' => ['index', 'show']]);
  }

  public function index()
  {
    try {
      $exist = Penyewaan::all();
      if (!$exist) {
        return HttpResponse::not_found();
      }
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::not_found($th->getMessage());
    }
  }

  public function store(Request $request)
  {
    try {
      $this->validate($request, $this->rules);
      $exist = Penyewaan::where('tanggal_mulai', '<=', $request->tanggal_mulai)
        ->where('tanggal_selesai', '>=', $request->tanggal_mulai)
        ->orWhere('tanggal_mulai', '<=', $request->tanggal_selesai)
        ->where('tanggal_selesai', '>=', $request->tanggal_selesai)
        ->first();

      if ($exist) {
        return HttpResponse::error('Rental is not available');
      }

      $file = $request->file('lampiran');
      $lampiran = $this->uploadFile($file);

      $response = Penyewaan::create([
        'tanggal_pengajuan' => $request->tanggal_pengajuan,
        'penanggung_jawab' => $request->penanggung_jawab,
        'asal_surat' => $request->asal_surat,
        'jenis_surat' => $request->jenis_surat,
        'type' => $request->type,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'lampiran' => $lampiran
      ]);
      return HttpResponse::success($response);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function show($id)
  {
    try {
      $exist = Penyewaan::find($id);
      if (!$exist) {
        return HttpResponse::not_found();
      }
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $this->validate($request, $this->rules);

      $exist = Penyewaan::where('tanggal_mulai', '<=', $request->tanggal_mulai)
        ->where('tanggal_selesai', '>=', $request->tanggal_mulai)
        ->orWhere('tanggal_mulai', '<=', $request->tanggal_selesai)
        ->where('tanggal_selesai', '>=', $request->tanggal_selesai)
        ->first();

      if ($exist) {
        return HttpResponse::error('Rental is not available');
      }

      $exist = Penyewaan::find($id);
      if (!$exist) {
        return HttpResponse::not_found();
      }

      $file = $request->file('lampiran');
      $this->deletedFile($exist->lampiran);
      $lampiran = $this->uploadFile($file);

      $exist->update([
        'tanggal_pengajuan' => $request->tanggal_pengajuan,
        'penanggung_jawab' => $request->penanggung_jawab,
        'asal_surat' => $request->asal_surat,
        'jenis_surat' => $request->jenis_surat,
        'type' => $request->type,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'lampiran' => $lampiran
      ]);
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function destroy($id)
  {
    try {
      $exist = Penyewaan::find($id);
      if (!$exist) {
        return HttpResponse::not_found();
      }
      $exist->delete();
      return HttpResponse::success($exist);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function uploadFile($file) {
    if($file) {
      $fileName = time() . '.' . $file->getClientOriginalExtension();
      $file->move('uploads', $fileName);
      return "/uploads/" . $fileName;
    }
    return null;
  }

  public function deletedFile($file) {
    if($file) {
      $fileName = explode('/', $file);
      $fileName = $fileName[count($fileName) - 1];
      $path = "/uploads/" . $fileName;
      if(file_exists($path)) {
        unlink($path);
      }
    }
  }
}
