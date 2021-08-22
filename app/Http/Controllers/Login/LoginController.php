<?php
namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Models\LoginModel;

class LoginController extends Controller
{
  public function login(Request $req){
    // print_r($req->headers->all()['token'][0]);
    // die();
    $data = $req->all();
    $one = $data['user_id'];
    $two = $data['lesson_id'];
    $three = time();
    $four = $data['moderator'];
    // $four = $req->headers->all()['moderator'][0];

    $res = $this->checkAuthByUserId($one);

    if($res == 1){
      $users = DB::connection()->getPdo()->exec("INSERT INTO `auth` (`user_id`, `lesson_id`, `date`, `moderator`) VALUES ($one, $two, $three, $four)");
      $respose['message'] = 'Успешно';
      return response()->json($respose, 201);
    }else{
      $current = time();
      $deference = ($current - $res[0]->date) / 3600;
      $user['error'] = 'По данному QR-коду пользователь сегодня проходил авторизацию в '.date("H:i:s", $res[0]->date+( 3600 * 5));
      $user['data'] = date("H:i:s", $res[0]->date+( 3600 * 5));
      return response()->json($user, 403);
    }
  }

  public function checkAuthByUserId($id){
    $users = DB::select("SELECT * FROM `auth` WHERE `user_id` = $id");
    if(count($users) > 0){
      return $users;
    }else{
      return 1;
    }
  }

  public function getAllBusinessman(){
    $users = DB::select("SELECT b.*, a.moderator, a.date FROM `businessman` as b, `auth` as a WHERE a.user_id = b.telephone");
    return response()->json($users, 200);
  }
}