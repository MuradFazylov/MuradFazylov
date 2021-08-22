<?php

namespace App\Http\Controllers\Couching;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class BranchesController extends Controller
{
  public function getBranches(){
    $lang = 'uz';
    $branches = DB::select("SELECT id, branch_$lang as `branch` FROM `branches`");
    return response()->json($branches, 200);
  }

  public function getReasons($type){
    $reasons = DB::select("SELECT * FROM `reasons` WHERE `type` = '$type'");
    return response()->json($reasons, 200);
  }

  public function setAbsence(Request $req){
    $data = $req->all();
    $id = addslashes($data['id']);
    $lesson = addslashes($data['lesson']);
    $reason = addslashes($data['reason']);
    $date_create = time();

    $reasons = DB::select("SELECT id FROM `activity` WHERE `user_id` = '$id'");

    $activity_id = $reasons[0]->id;

    $query = DB::connection()->getPdo()->exec(
      "DELETE FROM `absence` WHERE `user_id` = '$id' AND `lesson_id` = '$lesson'"
    );

    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `absence` (`activity_id`, `user_id`, `lesson_id`, `reason_id`, `date`) 
      VALUES ('$activity_id', '$id', '$lesson', '$reason', '$date_create')"
    );
    if($query > 0){
      $response['message'] = 'Причина указана';
      return response()->json($response, 201);
    }else{
      $response['message'] = 'Неуспешно';
      return response()->json($response, 400);
    }
  }
}
