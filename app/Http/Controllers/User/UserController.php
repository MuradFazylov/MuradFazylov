<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Models\UserModel;

class UserController extends Controller
{
  
  public function users(Request $requst){
    $users = DB::select("SELECT * FROM `users` ORDER BY `id` DESC");
    return response()->json($users, 200);
  }
  public function userById($id){
    $users = DB::select("SELECT * FROM `users` WHERE `id` = $id");
    return response()->json($users, 200);
  }

  public function editUser(Request $requst){
    $data = $requst->all();
    $id = $data['id'];
    $name = addslashes($data['name']);
    $surname = addslashes($data['surname']);
    $sex = $data['sex'];
    $telephone = $data['telephone'];
    $region_id = $data['region_id'];
    $role = $data['role'];
    $moderator_id = $data['moderator_id'];
    $img = $data['img'];
    $query = DB::connection()->getPdo()->exec(
      "UPDATE `users` SET 
      `name`='$name',
      `surname`='$surname',
      `sex`='$sex',
      `telephone`='$telephone',
      `region_id`='$region_id',
      `role`='$role',
      `img`='$img'
      WHERE `id`= '$id'"
    );
    if($query == 1){
      $this->setLog(1, 'Пользователь с id = '.$id.' был изменен пользователем'.$moderator_id, $id, time());
      $response['message'] = 'Пользователь изменен';
      return response()->json($response, 201);
    } 
  }

  public function uploadAvatar(Request $req){
    $filename = time().".".$req->file('img')->getClientOriginalExtension();
    $result = $req->file('img')->move(public_path('uploads/users'), $filename);
    $respose = ["filename"=>$filename];
    return response()->json($respose, 201);
  }

  public function deleteUser($id){
    $query = DB::connection()->getPdo()->exec("UPDATE `users` SET `status`='deleted' WHERE `id`=$id");
    if($query == 1){
      $this->setLog(1, 'Пользователь с id = '.$id.' был удален пользователем 1 по причине', $id, time());
      $response['message'] = 'Пользователь удален';
      return response()->json($response, 201);
    } 
  }

  public function blockUser($id){
    $query = DB::connection()->getPdo()->exec("UPDATE `users` SET `status`='blocked' WHERE `id`=$id");
    if($query == 1){
      $this->setLog(1, 'Пользователь с id = '.$id.' был заблокирован пользователем 1 по причине', $id, time());
      $response['message'] = 'Пользователь заблокирован';
      return response()->json($response, 201);
    }  
  }

  public function activateUser($id){
    $query = DB::connection()->getPdo()->exec("UPDATE `users` SET `status`='active' WHERE `id`=$id");
    if($query == 1){
      $this->setLog(1, 'Пользователь с id = '.$id.' был разблокирован пользователем 1 по причине', $id, time());
      $response['message'] = 'Права пользователя восстановлены';
      return response()->json($response, 201);
    } 
  }

  public function destroyToken(Request $req){
    $data = $req->all();
    $token = $data['token'];
    $query = DB::connection()->getPdo()->exec("UPDATE `authorization` SET `status`='destroyed' WHERE `token`='$token'");
    if($query == 1){
      $response['message'] = 'Logout';
      return response()->json($response, 201);
    } 
  }

  public function userByToken(Request $req){
    $data = $req->all();
    $token = $data['token'];
    $user_id = DB::select("SELECT user_id FROM `authorization` WHERE `token` = '$token' AND `status` = 'active'");
    if(count($user_id) > 0){
      $id = $user_id[0]->user_id;
      $user = DB::select("SELECT * FROM `users` WHERE `id` = '$id'");
      return response()->json($user[0], 200);
    }else{
      $response['message'] = 'Недействительный ключ';
      return response()->json($response, 404);
    }
    
  }

  public function userByPhone($phone){
    $user = DB::select("SELECT * FROM `businessman` WHERE `telephone` = $phone");
    if(count($user) > 0){
      return response()->json($user, 200);
    }else{
      $user['error'] = 'Пользователь не найден или данные указаны неверно';
      return response()->json($user, 404);
    }
  }

  public function userByRole($id){
    $user = DB::select("SELECT * FROM `users` WHERE `role` = $id");
    if(count($user) > 0){
      return response()->json($user, 200);
    }else{
      $user['error'] = 'Пользователь не найден или данные указаны неверно';
      return response()->json($user, 404);
    }
  }

  public function create(Request $req){
    $data = $req->all();
    $name = $data['name'];
    $surname = $data['surname'];
    $sex = $data['sex'];
    $region_id = $data['region_id'];
    $role = $data['role'];
    $status = $data['status'];
    $telephone = $data['telephone'];
    $create_date = time();
    
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `users` (`name`, `surname`, `sex`, `region_id`, `role`, `status`, `telephone`, `create_date`) 
      VALUES ('$name', '$surname', '$sex', '$region_id', '$role', '$status', '$telephone', '$create_date')"
    );
    if($query > 0){
      $userId = DB::select("SELECT LAST_INSERT_ID()");
      $login = strtolower($name).".".strtolower($surname);
      $this->createLogin($userId, $login);
      $respose['message'] = 'Успешно! Пользователь успешно добавлен';
      
      // return response()->json($respose, 201);
    }else{
      $respose['message'] = 'Неуспешно';
      return response()->json($respose, 400);
    }
  }

  public function createLogin($id, $login){
    $id = (array)$id[0];
    $id = $id['LAST_INSERT_ID()'];
    $user_id = $id;
    $login = $login;
    $password = md5('123456');
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `passwords` (`user_id`, `login`, `password`) 
      VALUES ('$user_id', '$login', '$password')"
      );
    $respose['message'] = 'Успешно! Пароль добавлен';
    return response()->json($respose, 201);
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
}
