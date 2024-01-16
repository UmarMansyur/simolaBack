<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

class Pagination {
  public static function init($model, $params) {
    $params['limit'] = isset($params['limit']) ?(intval($params['limit'])) : 10;
    $params['page'] = isset($params['page']) ? intval($params['page']) : 1;
    $offset = intval($params['page'] - 1) * intval($params['limit']);
    // $totalPage = ceil($model::count() / intval($params['limit']));
    // $totalRows = $model::count();
    $exist = null;
    if($params['status']) {
      $exist = $model::where('status', $params['status']);
    }
    if($params['nomor_induk']) {
      $exist = $model::where('type', $params['type']);
    }
    $exist = $exist->offset($offset)->limit($params['limit']);
    if($model == 'App\\Models\\Penyewaan'){
      $exist = $exist->orderBy('tanggal_pengajuan', 'desc')->get();
    } else {
      $exist = $exist->get();
    }

    $totalPage = null;
    if($params['status']) {
      $totalPage = $model::where('status', $params['status']);
    }
    if($params['nomor_induk']) {
      $totalPage = $model::where('type', $params['type']);
    }
    $totalPage = ceil($totalPage->count() / intval($params['limit']));

    $totalRows = null;
    if($params['status']) {
      $totalRows = $model::where('status', $params['status']);
    }
    if($params['nomor_induk']) {
      $totalRows = $model::where('type', $params['type']);
    }
    $totalRows = $totalRows->count();

    if (!$exist) {
      return HttpResponse::not_found();
    }

    $result = [
      'page' => $params['page'],
      'limit' => $params['limit'],
      'total_page' => $totalPage,
      'total_rows' => $totalRows,
      'data' => $exist,
      'model' => $model,
    ];

    return HttpResponse::success($result);
  }


  public static function initWithSearch($model, $params, $search = []) {
    $params['limit'] = isset($params['limit']) ?(intval($params['limit'])) : 10;
    $params['page'] = isset($params['page']) ? intval($params['page']) : 1;
    $offset = intval($params['page'] - 1) * intval($params['limit']);

    $exist = $model::where(function($query) use ($params, $search) {
      foreach ($search as $key => $value) {
        $query->orWhere($value, 'like', '%'.$params['search'].'%');
      }
    });

    if(request()->get('status')) {
      $exist = $exist->where('status', request()->get('status'));
    }
    if(request()->get('nomor_induk')) {
      $exist = $exist->where('type', request()->get('type'));
    }
    $exist = $exist->offset($offset)->limit($params['limit'])->get();

    $totalPage = $model::where(function($query) use ($params, $search) {
      foreach ($search as $key => $value) {
        $query->orWhere($value, 'like', '%'.$params['search'].'%');
      }
    });

    if(request()->get('status')) {
      $totalPage = $totalPage->where('status', request()->get('status'));
    }
    if(request()->get('nomor_induk')) {
      $totalPage = $totalPage->where('type', request()->get('type'));
    }
    $totalPage = ceil($totalPage->count() / intval($params['limit']));
    $totalRows = $model::where(function($query) use ($params, $search) {
      foreach ($search as $key => $value) {
        $query->orWhere($value, 'like', '%'.$params['search'].'%');
      }
    });

    if(request()->get('status')) {
      $totalRows = $totalRows->where('status', request()->get('status'));
    }
    if(request()->get('nomor_induk')) {
      $totalRows = $totalRows->where('type', request()->get('type'));
    }
    $totalRows = $totalRows->count();

    if (!$exist) {
      return HttpResponse::not_found();
    }
    $result = [
      'page' => $params['page'],
      'limit' => $params['limit'],
      'total_page' => $totalPage,
      'total_rows' => $totalRows,
      'data' => $exist,
    ];

    return HttpResponse::success($result);
  }
}