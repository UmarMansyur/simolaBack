<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Utils\HttpResponse;
use App\Utils\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyewaanController extends Controller
{
  public function __construct()
  {
    $this->rules = [
      'kegiatan' => 'required|string',
      'tanggal_pengajuan' => 'date',
      'penanggung_jawab' => 'required|string',
      'asal_surat' => 'required|string',
      'jenis_surat' => 'required|string',
      'type' => 'required|string',
      'tanggal_mulai' => 'required|date',
      'tanggal_selesai' => 'required|date',
      'lampiran' => 'file|mimes:pdf,doc,docx',
      'status' => 'string',
      'tanggal_persetujuan_bau' => 'date',
      'tanggal_persetujuan_kepala_bagian_umum' => 'date'
    ];
    $this->middleware('auth', ['except' => ['index', 'show']]);
  }

  public function index()
  {
    try {
      $request = request()->all();
      if (isset($request['search'])) {
        return Pagination::initWithSearch(Penyewaan::class, $request, ['kegiatan', 'tanggal_pengajuan', 'penanggung_jawab', 'asal_surat', 'jenis_surat', 'type', 'tanggal_mulai', 'tanggal_selesai', 'lampiran']);
      }
      return Pagination::init(Penyewaan::class, request()->all());
    } catch (\Throwable $th) {
      return HttpResponse::not_found($th->getMessage());
    }
  }

  public function store(Request $request)
  {
    try {
      $this->validate($request, $this->rules);
      if ($request->tanggal_mulai >= $request->tanggal_selesai) {
        return HttpResponse::error('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
      }
      $exist = DB::select("
      SELECT *
      FROM penyewaan
      WHERE type = ?
          AND (
              (? >= tanggal_mulai AND ? < tanggal_selesai)
              OR (? > tanggal_mulai AND ? <= tanggal_selesai)
              OR (tanggal_mulai >= ? AND tanggal_selesai <= ?)
          )
          AND (tanggal_pengajuan >= ? AND tanggal_pengajuan <= ?)
  ", [
        $request->type,
        $request->tanggal_mulai,
        $request->tanggal_mulai,
        $request->tanggal_selesai,
        $request->tanggal_selesai,
        $request->tanggal_mulai,
        $request->tanggal_selesai,
        $request->tanggal_pengajuan,
        $request->tanggal_selesai
      ]);

      if ($exist) {
        return HttpResponse::error('Penyewaan sudah ada');
      }

      $file = $request->file('lampiran');
      $lampiran = $this->uploadFile($file);

      $response = Penyewaan::create([
        'kegiatan' => $request->kegiatan,
        'tanggal_pengajuan' => $request->tanggal_pengajuan,
        'penanggung_jawab' => $request->penanggung_jawab,
        'asal_surat' => $request->asal_surat,
        'jenis_surat' => $request->jenis_surat,
        'type' => $request->type,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'lampiran' => $lampiran,
        'status' => $request->status,
        'tanggal_persetujuan_bau' => $request->tanggal_persetujuan_bau ? $request->tanggal_persetujuan_bau : null,
        'tanggal_persetujuan_kepala_bagian_umum' => $request->tanggal_persetujuan_kepala_bagian_umum ? $request->tanggal_persetujuan_kepala_bagian_umum : null
      ]);
      return HttpResponse::success($response, 'Penyewaan berhasil ditambahkan');
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

      $exist = Penyewaan::find($id);
      if (!$exist) {
        return HttpResponse::not_found();
      }

      $file = $request->file('lampiran');
      $lampiran = $exist->lampiran;
      if($file) {
        $this->deletedFile($exist->lampiran);
        $lampiran = $this->uploadFile($file);
      }

      if($exist->status == 'Selesai') {
        return HttpResponse::error('Penyewaan sudah selesai');
      }

      if ($request->tanggal_mulai >= $request->tanggal_selesai) {
        return HttpResponse::error('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
      }

      // jika disetujui bau, maka tidak bisa diubah lagi
      if ($exist->status == 'Disetujui BAU') {
        return HttpResponse::error('Penyewaan sudah disetujui BAU');
      }

      if ($exist->status == 'Disetujui Kepala Bagian Umum') {
        return HttpResponse::error('Penyewaan sudah disetujui Kepala Bagian Umum');
      }

      if($exist->status == 'Ditolak BAU') {
        return HttpResponse::error('Penyewaan sudah ditolak BAU');
      }

      if($exist->status == 'Ditolak Kepala Bagian Umum') {
        return HttpResponse::error('Penyewaan sudah ditolak Kepala Bagian Umum');
      }

      $exist->update([
        'tanggal_pengajuan' => $request->tanggal_pengajuan,
        'penanggung_jawab' => $request->penanggung_jawab,
        'asal_surat' => $request->asal_surat,
        'jenis_surat' => $request->jenis_surat,
        'type' => $request->type,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'lampiran' => $lampiran,
        'kegiatan' => $request->kegiatan,
        'status' => $request->status,
        'tanggal_persetujuan_bau' => $request->tanggal_persetujuan_bau ? $request->tanggal_persetujuan_bau : null,
        'tanggal_persetujuan_kepala_bagian_umum' => $request->tanggal_persetujuan_kepala_bagian_umum ? $request->tanggal_persetujuan_kepala_bagian_umum : null
      ]);
      return HttpResponse::success($exist, 'Penyewaan berhasil diubah');
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

  public function uploadFile($file)
  {
    if ($file) {
      $fileName = time() . '.' . $file->getClientOriginalExtension();
      $file->move('uploads', $fileName);
      return "/uploads/" . $fileName;
    }
    return null;
  }

  public function deletedFile($file)
  {
    if ($file) {
      $fileName = explode('/', $file);
      $fileName = $fileName[count($fileName) - 1];
      $path = "/uploads/" . $fileName;
      if (file_exists($path)) {
        unlink($path);
      }
    }
  }
}
