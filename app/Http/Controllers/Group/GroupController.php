<?php

namespace App\Http\Controllers\Group;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class GroupController extends Controller
{
  public function getGroups(Request $requst){
    $groups = DB::select("SELECT * FROM `group`");
    $businessmen = DB::select("SELECT * FROM `businessman` WHERE `status` <> 'deleted'");
    for($i = 0; $i < count($groups); $i++){
      $groups[$i]->amount = 0;
    }
    for($i = 0; $i < count($businessmen); $i++){
      for($j = 0; $j < count($groups); $j++){
        if($businessmen[$i]->group_id == $groups[$j]->name){
          $groups[$j]->amount++;
        }
      }
    }
    return response()->json($groups, 200);
  }

  public function getGroupsByMentor($id){
    $groups = DB::select("SELECT * FROM `group` WHERE `mentor` = '$id'");
    return response()->json($groups, 200);
  }

  public function create(Request $req){   
    $data = $req->all();
    $name = $data['number'];
    $mentor =$data['mentor'];
    $couching = $data['couching'];
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `group` (`name`, `mentor`, `couching`) 
      VALUES ('$name', '$mentor', '$couching')"
      );
    $respose['message'] = 'Успешно! Группа добавлено';
    return response()->json($respose, 201);
  }

  public function groupMentorSet(Request $req){
    $data = $req->all();
    $group_id = $data['group_id'];
    $mentor_id =$data['mentor_id'];

    $query = DB::connection()->getPdo()->exec(
      "UPDATE `group` SET `mentor` = '$mentor_id' WHERE `name`= '$group_id' AND `couching` = 13
    ");
    $respose['message'] = 'Ментор добавлен';
    return response()->json($respose, 201);
  }

  public function setGroupsAuto(){
    $query = DB::connection()->getPdo()->exec(
      "UPDATE `group` SET `amount` = 0 WHERE `couching` = 13"
    );
    $users = DB::select("SELECT * FROM `businessman` WHERE `status`<> 'deleted'");
    // return response()->json($users, 200);
    for($i = 0; $i < count($users); $i++){
      $group = $users[$i]->group_id;
      $query = DB::connection()->getPdo()->exec(
        "UPDATE `group` SET `amount` = `amount`+1 WHERE `name`= '$group' AND `couching` = 13");
    }

    
    $respose['message'] = 'success';
    return response()->json($respose, 201);
  }
}
