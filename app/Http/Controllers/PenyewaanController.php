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
      $request = request()->all();
      $limit = $request['limit'] ?? 10;
      $page = $request['page'] ?? 1;
      $offset = intval($page - 1) * intval($limit);

      
      $exist = Penyewaan::offset($offset)->limit($limit)->get();
      $totalRows = Penyewaan::count();
      if(!$exist) {
        return HttpResponse::not_found();
      }

      
     if(!empty($request['search'])) {
        $exist = Penyewaan::where('nomor_induk', 'like', '%'.$request['search'].'%')
        ->orWhere('type_user', 'like', '%'.$request['search'].'%')
        ->orWhere('kegiatan', 'like', '%'.$request['search'].'%')
        ->orWhere('tanggal_pengajuan', 'like', '%'.$request['search'].'%')
        ->orWhere('penanggung_jawab', 'like', '%'.$request['search'].'%')
        ->orWhere('asal_status', 'like', '%'.$request['search'].'%')
        ->orWhere('jenis_surat', 'like', '%'.$request['search'].'%')
        ->orWhere('type', 'like', '%'.$request['search'].'%')
        ->orWhere('tanggal_persetujuan_bau', 'like', '%'.$request['search'].'%')
        ->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count();
      }

      if(!empty($request['status'])) {
        $exist = Penyewaan::where('status', $request['status'])->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count();
        if($request['status'] == 'sudah') {
          $exist = Penyewaan::where('status', '!=', 'Menunggu Persetujuan')->offset($offset)->limit($limit)->get();
          $totalRows = $exist->count();
        }
      }
      
      if(!empty($request['type']) && !empty($request['status'])) {
        $exist = Penyewaan::where('type', $request['type'])->where('status', $request['status'])->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count();
        if($request['status'] == 'sudah') {
          $exist = Penyewaan::where('type', $request['type'])->where('status', '!=', 'Menunggu Persetujuan')->offset($offset)->limit($limit)->get();
          $totalRows = $exist->count();
        }
      }

      if(!empty($request['status']) && !empty($request['nomor_induk'])) {
        $exist = Penyewaan::where('status', $request['status'])->where('nomor_induk', $request['nomor_induk'])->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count(); 

        if($request['status'] == 'sudah') {
          $exist = Penyewaan::where('status', '!=', 'Menunggu Persetujuan')->where('nomor_induk', $request['nomor_induk'])->offset($offset)->limit($limit)->get();
          $totalRows = $exist->count();
        }
      }


      if(!empty($request['type']) && !empty($request['status']) && !empty($request['nomor_induk'])) {
        $exist = Penyewaan::where('type', $request['type'])->where('status', $request['status'])->where('nomor_induk', $request['nomor_induk'])->offset($offset)->limit($limit)->get();
        $totalRows = $exist->count();
        if($request['status'] == 'sudah') {
          $exist = Penyewaan::where('type', $request['type'])->where('status', '!=', 'Menunggu Persetujuan')->where('nomor_induk', $request['nomor_induk'])->offset($offset)->limit($limit)->get();
          $totalRows = $exist->count();
        }
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
          AND (tanggal_pengajuan >= ? AND tanggal_pengajuan <= ?) AND jenis_surat = ?
  ", [
        $request->type,
        $request->tanggal_mulai,
        $request->tanggal_mulai,
        $request->tanggal_selesai,
        $request->tanggal_selesai,
        $request->tanggal_mulai,
        $request->tanggal_selesai,
        $request->tanggal_pengajuan,
        $request->tanggal_selesai,
        $request->jenis_surat
      ]);

      if ($exist) {
        return HttpResponse::error('Penyewaan sudah ada');
      }

      $file = $request->file('lampiran');
      $lampiran = $this->uploadFile($file);

      $response = Penyewaan::create([
        'nomor_induk' => $request->nomor_induk,
        'type_user' => $request->type_user,
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

  public function statusPeminjaman(Request $request, $id) {
    try {
      $exist = Penyewaan::find($id);
      if (!$exist) {
        return HttpResponse::not_found();
      }

      if($exist->status == 'Selesai') {
        return HttpResponse::error('Penyewaan sudah selesai');
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

      if($request->status == 'Disetujui BAU') {
        $exist->update([
          'status' => $request->status,
          'disposisi' => $request->keterangan,
          'tanggal_persetujuan_bau' => date('Y-m-d')
        ]);
      }

      if($request->status == 'Disetujui Kepala Bagian Umum') {
        $exist->update([
          'status' => $request->status,
          'tanggal_persetujuan_kepala_bagian_umum' => date('Y-m-d')
        ]);
      }

      if($request->status == 'Ditolak BAU') {
        $exist->update([
          'status' => $request->status,
          'keterangan' => $request->keterangan,
          'tanggal_persetujuan_bau' => null
        ]);
      }

      if($request->status == 'Ditolak Kepala Bagian Umum') {
        $exist->update([
          'status' => $request->status,
          'keterangan' => $request->keterangan,
          'tanggal_persetujuan_kepala_bagian_umum' => null
        ]);
      }

      return HttpResponse::success($exist, 'Status penyewaan berhasil diubah');
    } catch (\Throwable $th) {
      //throw $th;
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
