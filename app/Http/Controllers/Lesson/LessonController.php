<?php

namespace App\Http\Controllers\Lesson;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class LessonController extends Controller
{
  public function create(Request $req){   
    $data = $req->all();
    $lesson = $data['lesson'];
    $theme = addslashes($data['theme']);
    $couching = $data['couching'];
    $date = $data['date'];
    $moderator_id = $data['moderator_id'];
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `lessons` (`lesson`, `theme`, `moderator_id`, `date`, `couching`) 
      VALUES ('$lesson', '$theme', '$moderator_id', '$date', '$couching')"
      );
    $respose['message'] = 'Успешно! Занятие добавлено';
    return response()->json($respose, 201);
  }

  public function lessons($couching){
    $users = DB::select("SELECT *, FROM_UNIXTIME(date ,'%d.%m.%Y') as date FROM `lessons` WHERE `couching`=$couching");
    return response()->json($users, 200);
  }

  public function delete($id){
    $request = DB::connection()->getPdo()->exec("DELETE FROM `lessons` WHERE `id`=$id");
    if($request > 0){
      $response['message'] = 'Успешно! Занятие удалено';
      return response()->json($response, 200);
    }else{
      $response['message'] = 'Указанное занятие не найдено';
      return response()->json($response, 404);
    }
  }
}
