<?php

namespace App\Http\Controllers\Statistic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class DeletedController extends Controller
{
  public function getAllDeletedBusinessmen(){
    $reasons = DB::select("SELECT * FROM `reasons` WHERE `type` = 'delete'");
    $businessmen = DB::select("SELECT * FROM `deleted`");

    for($i = 0; $i < count($reasons); $i++){
      $reasons[$i]->count = 0;
      for($j = 0; $j < count($businessmen); $j++){
        if($businessmen[$j]->reason_id == $reasons[$i]->id){
          $reasons[$i]->count++;
        }
      }
    }
    for($i = 0; $i < count($reasons); $i++){
      if($reasons[$i]->count == 0){
        array_splice($reasons, $i, 1);
        $i = 0;
      }
    }
    return response()->json($reasons, 200);
  }

  public function checkDeleted(){
    $activity = DB::select("SELECT * FROM `activity` WHERE `lesson_7` <> 'd'");
    $businessmen = DB::select("SELECT * FROM `businessman` WHERE `status` = 'deleted'");

    $tmp_array = [];
    for($i = 0; $i < count($activity); $i++){
      $tmp = false;
      for($j = 0; $j < count($businessmen); $j++){
        if($businessmen[$j]->id == $activity[$i]->user_id){
          $tmp = true;
          array_push($tmp_array, $activity[$i]);
          break;
        }
      }
      // if($tmp == false){
      //   array_push($tmp_array, $activity[$i]);
      // }
    }
    // print_r($tmp_array);
    // die();
    $response['count'] = count($tmp_array);
    $response['data'] = $tmp_array;
    return response()->json($response, 200);
  }
}
