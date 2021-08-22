<?php

namespace App\Http\Controllers\Couching;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class CouchingController extends Controller
{
  public function couching(){
    $query = DB::select("SELECT * FROM `couching`");
    return response()->json($query, 200);
  }

  public function create(Request $req){   
    $data = $req->all();
    $name = $data['name'];
    $year = $data['year'];
    $amount_of_students = $data['amount_of_students'];
    $moderator_id = $data['moderator_id'];
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `couching` (`name`, `year`, `moderator_id`, `amount_of_students`) 
      VALUES ('$name', '$year', '$moderator_id', '$amount_of_students')"
      );
    $respose['message'] = 'Успешно! Коучинг добавлен';
    return response()->json($respose, 201);
  }
}
