<?php

namespace App\Http\Controllers\Businessman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class CreateController extends Controller
{
  public function activityProcessing(){
    $businessman = DB::select("SELECT * FROM `businessman` WHERE `status` <> 'deleted'");
    $activity = DB::select("SELECT * FROM `activity`");
    
    for($i = 0; $i < count($businessman); $i++){
      $tmp = false;
      for($j = 0; $j < count($activity); $j++){  
        if($businessman[$i]->id == $activity[$j]->user_id){
          $tmp = true;
          break;
        }
      }
      if($tmp == false){
        $selected = $businessman[$i];
        $name = addslashes($selected->name);
        $surname = addslashes($selected->surname);
        $query = DB::connection()->getPdo()->exec(
          "INSERT 
          INTO `activity` (`user_id`, `name`, `surname`, `group_id`) 
          VALUES ('$selected->id', '$name', '$surname', '$selected->group_id')"
        );
      }
    }

    $data['message'] = 'Успешно';
    return response()->json($data, 200);
  }
}
