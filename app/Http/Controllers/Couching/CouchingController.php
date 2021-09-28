<?php

namespace App\Http\Controllers\Couching;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class CouchingController extends Controller
{
  public function couching(){
    $query = DB::select("SELECT * FROM `couching` WHERE `visible` = 'true'");
    return response()->json($query, 200);
  }

  public function create(Request $req){   
    $data = $req->all();
    $name = $data['name'];
    $year = $data['year'];
    $moderator_id = $data['moderator_id'];
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `couching` (`name`, `year`, `moderator_id`) 
      VALUES ('$name', '$year', '$moderator_id')"
      );
    $respose['message'] = 'Успешно! Коучинг добавлен';
    return response()->json($respose, 201);
  }

  public function getLastCouching(){
    $query = DB::select("SELECT * FROM `couching` ORDER BY id DESC LIMIT 1");
    $response['name'] = $query[0]->name;
    $response['value'] = $query[0]->value;
    return response()->json($response, 200);
  }

  public function setCouchingNumber(){
    $query = DB::connection()->getPdo()->exec(
      "UPDATE `businessman` SET `couching_value` = 13  WHERE `id`<> 0"
    );
  }
}
