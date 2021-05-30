<?php
namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Models\LoginModel;

class LoginController extends Controller
{
  public function login(Request $req){
    
    $data = $req->all();
    $one = $data['user_id'];
    $two = $data['lesson_id'];
    $three = time();

    $res = $this->usersById($one);
    if($res == 1){
      $users = DB::select("INSERT INTO `auth` (`user_id`, `lesson_id`, `date`) VALUES ($one, $two, $three)");
      return response()->json($users, 201);
    }else{
      $user['error'] = 'Пользователь уже прошел проверку';
      return response()->json($user, 400);
    }
  }

  public function usersById($id){
    $users = DB::select("SELECT * FROM `auth` WHERE `user_id` = $id");
    // print_r(count($users));
    // die();
    if(count($users) > 0){
      return 0;
    }else{
      return 1;
    }
    // die();
    // return response()->json(UserModel::get(), 200);
  }
}
