<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Models\UserModel;

class UserController extends Controller
{
  
  public function users(){
    // header('Content-type: json/application');
    // header('Access-Control-Allow-Origin: *');
    // $users = DB::select("SELECT * FROM `businessman` WHERE `sex` = 'Ayol' LIMIT 3");
    $users = DB::select("SELECT * FROM `businessman` WHERE `sex` = 'Erkak'");
    return response()->json($users, 200);
    // die();
    // return response()->json(UserModel::get(), 200);
  }
  public function usersById($id){
    $users = DB::select("SELECT * FROM `businessman` WHERE `date_create` = $id");
    return response()->json($users, 200);
    // die();
    // return response()->json(UserModel::get(), 200);
  }
}
