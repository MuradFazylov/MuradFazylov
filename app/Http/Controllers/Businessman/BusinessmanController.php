<?php

namespace App\Http\Controllers\Businessman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Storage;
use Response;
use File;
use ZipArchive;

class BusinessmanController extends Controller
{
  public function getDeletedById($id){
    $users = DB::select("SELECT * FROM `deleted` WHERE `user_id` = $id");
    $reason = DB::select("SELECT * FROM `reasons` WHERE `type` = 'delete'");
    if(count($users) > 0){
      for($i = 0; $i < count($reason); $i++){
        if($users[0]->reason_id == $reason[$i]->id){
          $users[0]->reason_id = $reason[$i]->title;
          break;
        }
      }
      return response()->json($users, 200);
    }else{
      $response['message'] = 'Not Found';
      return response()->json($response, 404);
    }
  }
  
  public function getBusinessmen(Request $req){  
    $users = DB::select("SELECT * FROM `businessman` WHERE `status` <> 'deleted' AND `couching_value` = $req->couching ORDER BY `id` DESC");
    return response()->json($users, 200);
  }

  public function getBusinessmenByStatus(Request $req, $status){
    // print_r($req->test);
    // print_r($status);
    // die();
    if($status != 'blacklist'){
      $users = DB::select("SELECT * FROM `businessman` WHERE `status` = '$status' AND `couching_value` = '$req->couching' ORDER BY `id` DESC");
    }
    if($status == 'blacklist'){
      $users = DB::select("SELECT * FROM `businessman` WHERE `blacklist` = '1' AND `couching_value` = '$req->couching' ORDER BY `id` DESC");
    }
    return response()->json($users, 200);
  }

  public function getAllQR(){
    $users = DB::select("SELECT qr FROM `businessman` WHERE `status` = 'offline'/* LIMIT 10*/");
    // $users = DB::table("businessman")->select(DB::raw('qr'))->where(`status` <> 'deleted' OR `status` <> 'online' OR `qr` IS NOT NULL)>get();
    $resp = [];
    foreach ($users as $user) {
      if($user->qr != null)
      array_push($resp, $user->qr);
    }
    $universal = '0000999999';
    array_push($resp, $universal);

    $response['data'] = $resp;
    return response()->json($response, 200);
  }

  public function getBusinessmanById($id){
    $users = DB::select("SELECT * FROM `businessman` WHERE `id` = $id");
    return response()->json($users, 200);
  }

  public function getBusinessmenByGroup($id){
    $users = DB::select("SELECT * FROM `businessman` WHERE `group_id` = $id AND `status` <> 'deleted'");
    return response()->json($users, 200);
  }

  public function addBusinessmanGroup(Request $req){
    $datas = $req->all();
    $datas = $datas['data'];
    $count = 0;
    for($i = 0; $i < count($datas); $i++){
      $data = $datas[$i];
      $name = addslashes($data['name']);
      $surname = addslashes($data['surname']);
      $sex = $data['sex'];
      $telephone = $data['telephone'];
      $region_id = $data['region_id'];
      $status = $data['status'];
      $payment = $data['payment'];
      $position = addslashes($data['position']);
      $work = addslashes($data['work']);
      $date_create = time();
      $query = DB::connection()->getPdo()->exec(
        "INSERT 
        INTO `businessman` (`name`, `surname`, `sex`, `telephone`, `region_id`, `status`, `date_create`, `payment`, `work`, `position`, `couching_value`) 
        VALUES ('$name', '$surname', '$sex', '$telephone', '$region_id', '$status', '$date_create', '$payment', '$work', '$position', '14')"
      );
      if($query = 1){
        $count++;
      }
    }
    if($count == count($datas)){
      $respose['message'] = 'Успешно! '.$count.' пользователей добавлено в базу';
      return response()->json($respose, 201);
    }
  }

  public function addBusinessman(Request $req){   
    $data = $req->all();
    $name = addslashes($data['name']);
    $surname = addslashes($data['surname']);
    $sex = $data['sex'];
    $telephone = $data['telephone'];
    $region_id = $data['region_id'];
    $img = $data['img'];
    $status = $data['status'];
    $moderator_id = $data['moderator_id'];
    $passport_scan = $data['passport'];
    $telegram = $data['telegram'];
    $instagram = $data['instagram'];
    $facebook = $data['facebook'];
    $payment = $data['payment'];
    $couching_value = $data['couching_value'];
    $date_create = time();
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `businessman` (`name`, `surname`, `sex`, `telephone`, `region_id`, `status`, `moderator_id`, `date_create`, `img`, `passport_scan`, `telegram`, `instagram`, `facebook`, `payment`, `couching_value`) 
      VALUES ('$name', '$surname', '$sex', '$telephone', '$region_id', '$status', '$moderator_id', '$date_create', '$img', '$passport_scan', '$telegram', '$instagram', '$facebook', '$payment', $couching_value)"
    );

    if(isset($group_id)){    
      if($user[0]->group_id != $group_id){
        $query = DB::connection()->getPdo()->exec(
          "UPDATE `group` SET `amount` = `amount`+1 WHERE `name`= '$group_id' AND `couching` = 13
        ");
      }   
    }

    $respose['message'] = 'Успешно! Пользователь добавлен';
    return response()->json($respose, 201);
  }

  public function updateBusinessman(Request $req){   
    $data = $req->all();
    $id = $data['id'];
    $name = addslashes($data['name']);
    $surname = addslashes($data['surname']);
    $sex = $data['sex'];
    $telephone = $data['telephone'];
    $region_id = $data['region_id'];
    $img = $data['img'];
    $status = $data['status'];
    $moderator_id = $data['moderator_id'];
    $passport_scan = $data['passport'];
    $telegram = addslashes($data['telegram']);
    $instagram = addslashes($data['instagram']);
    $facebook = addslashes($data['facebook']);
    $payment = $data['payment'];
    $email = addslashes($data['email']);
    $group_id = $data['group_id'];
    $work = addslashes($data['work']);
    $organisation = addslashes($data['organisation']);

    $point_a = addslashes($data['point_a']);
    $point_b = addslashes($data['point_b']);
    $before = addslashes($data['before']);
    $after = addslashes($data['after']);
    $branch = addslashes($data['branch']);
    $logo = addslashes($data['logo']);

    if(isset($group_id)){    
      $user = DB::select("SELECT * FROM `businessman` WHERE `id` = $id");
      $tmp = $user[0]->group_id;
      if($user[0]->group_id != $group_id){
        $query = DB::connection()->getPdo()->exec(
          "UPDATE `group` SET `amount` = `amount`+1 WHERE `name`= '$group_id' AND `couching` = 13
        ");
        $query = DB::connection()->getPdo()->exec(
          "UPDATE `group` SET `amount` = `amount`-1 WHERE `name`= '$tmp' AND `couching` = 13
        ");
      }   
    }
    $date_create = time();
    
    $query = DB::connection()->getPdo()->exec(
      "UPDATE `businessman` SET 
      `name` = '$name', 
      `surname` = '$surname', 
      `sex` = '$sex', 
      `telephone` = '$telephone', 
      `region_id` = '$region_id', 
      `img` = '$img', 
      `status` = '$status', 
      `moderator_id` = '$moderator_id', 
      `passport_scan` = '$passport_scan', 
      `telegram` = '$telegram', 
      `instagram` = '$instagram', 
      `facebook` = '$facebook', 
      `payment` = '$payment', 
      `last_update_time` = '$date_create', 
      `email` = '$email', 
      `group_id` = '$group_id', 
      `work` = '$work', 
      `organisation` = '$organisation',
      `point_a` = '$point_a',
      `point_b` = '$point_b',
      `before` = '$before',
      `after` = '$after',
      `logo` = '$logo',
      `branch` = '$branch'      
      WHERE `id`= $id
    ");
    $respose['message'] = 'Информация о пользователе обновлена';
    return response()->json($respose, 201);
  }

  public function blockBusinessman($id){
    $query = DB::connection()->getPdo()->exec("UPDATE `businessman` SET `status`='blocked' WHERE `id`=$id");
    if($query == 1){
      $response['message'] = 'Бизнесмен заблокирован';
      return response()->json($response, 200);
    }else{
      $response['message'] = 'Неуспешно';
      return response()->json($response, 200);
    }
  }

  public function blacklistBusinessman($id){
    $query = DB::connection()->getPdo()->exec("UPDATE `businessman` SET `status`='deleted', `blacklist`='1'  WHERE `id`=$id");
    if($query == 1){
      $response['message'] = 'Бизнесмен отправлен в черный список';
      return response()->json($response, 200);
    }else{
      $response['message'] = 'Неуспешно';
      return response()->json($response, 200);
    }
  }

  public function uploadAvatar(Request $req){
    $filename = time().".".$req->file('img')->getClientOriginalExtension();
    $result = $req->file('img')->move(public_path('uploads/avatars'), $filename);
    $respose = ["filename"=>$filename];
    return response()->json($respose, 201);
  }

  public function uploadLogo(Request $req){
    $filename = time().".".$req->file('img')->getClientOriginalExtension();
    $result = $req->file('img')->move(public_path('uploads/logo'), $filename);
    $respose = ["filename"=>$filename];
    return response()->json($respose, 201);
  }

  public function regions($lang){
    $query = DB::select("SELECT id, name_$lang as `name` FROM `regions`");
    return response()->json($query, 200);
  }

  public function setLog($user_id, $info, $object_id, $time){
    $query = DB::connection()->getPdo()->exec(
    "INSERT 
      INTO `logs` (`user_id`, `info`, `object_id`, `time`) 
      VALUES ('$user_id', '$info', '$object_id', '$time')"
    );
    $respose['message'] = 'Успешно! Пароль добавлен';
    return response()->json($respose, 201);
  }

  public function getBusinessmenGroupByRegions(){
    $businessmen = DB::select("SELECT * FROM `businessman` WHERE `status` <> 'deleted' ORDER BY `id` DESC");
    $regions = DB::select("SELECT id, name_ru as `name` FROM `regions`");

    $response = [];

    for($j = 0; $j < count($regions); $j++){
      $resp['id'] = $regions[$j]->id;
      $resp['name'] = $regions[$j]->name;
      $resp['offline'] = 0;
      $resp['online'] = 0;
      array_push($response, $resp);
    }

    for($i = 0; $i < count($businessmen); $i++){
      for($j = 0; $j < count($response); $j++){
        if($response[$j]['id'] == $businessmen[$i]->region_id && $businessmen[$i]->status == 'offline'){
          $response[$j]['offline']++;
        }
        if($response[$j]['id'] == $businessmen[$i]->region_id && $businessmen[$i]->status == 'online'){
          $response[$j]['online']++;
        }
      }
    }

    for($i = 0; $i < count($response); $i++){
      if($response[$i]['offline'] == 0 && $response[$i]['online'] == 0){
        array_splice($response, $i, 1);
        $i = 0;
      }
    }

    return response()->json($response, 200);
  }
}
