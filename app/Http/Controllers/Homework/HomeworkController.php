<?php

namespace App\Http\Controllers\Homework;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class HomeworkController extends Controller
{
  public function getAllHomework(){
    $homework = DB::select("SELECT * FROM `homework`");
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching`= '3'");
    $response = [];
    for($i = 0; $i < count($lessons); $i++){
      $tmp_arr['value'] = 0;
      $tmp_arr['lesson'] = $lessons[$i]->lesson;
      for($j = 0; $j < count($homework); $j++){
        if($homework[$j]->lesson_id == $lessons[$i]->lesson){
          $tmp_arr['value'] = $tmp_arr['value'] + $homework[$j]->value;
        }
      }
      array_push($response, $tmp_arr);
    }
    return response()->json($response, 200);
  }

  public function getHomeworkByMentor($id){
    $homework = DB::select("SELECT * FROM `homework`");
    $mentor = DB::select("SELECT * FROM `users` WHERE `id` = $id");
    $groups = DB::select("SELECT * FROM `group` WHERE `mentor` = $id");
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching` = 3");
    $response = [];
    for($i = 0; $i < count($lessons); $i++){
      $resp['value'] = 0;
      $resp['lesson'] = $lessons[$i]->lesson;
      for($j = 0; $j < count($homework); $j++){
        if($lessons[$i]->lesson == $homework[$j]->lesson_id){
          for($k = 0; $k < count($groups); $k++){
            if($homework[$j]->group_id == $groups[$k]->name){
              $resp['value'] = $resp['value'] + $homework[$j]->value;
            }
          }
        }
      }
      array_push($response, $resp);
    }
    return response()->json($response, 200);
  }

  public function getHomeworkByGroup($id){
    $homework = DB::select("SELECT * FROM `homework` WHERE `group_id` = '$id'");
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching` = 3");
    $response = [];
    for($i = 0; $i < count($lessons); $i++){
      $resp['value'] = 0;
      $resp['lesson'] = $lessons[$i]->lesson;
      for($j = 0; $j < count($homework); $j++){
        if($lessons[$i]->lesson == $homework[$j]->lesson_id){
          $resp['value'] = $resp['value'] + $homework[$j]->value;
        }
      }
      array_push($response, $resp);
    } 
    return response()->json($response, 200);
  }

  public function getHomeworkByLesson($id){
    $homework = DB::select("SELECT * FROM `homework` WHERE `lesson_id` = '$id'");
    $groups = DB::select("SELECT `name` FROM `group` ORDER BY CONVERT(SUBSTRING(`name`, 6), SIGNED INTEGER) ASC");

    
    for($i = 0; $i < count($groups)-1; $i++){
      for($j = $i; $j < count($groups); $j++){
        if($groups[$i]->name > $groups[$j]->name){
          $tmp = $groups[$i]->name;
          $groups[$i]->name = $groups[$j]->name;
          $groups[$j]->name = $tmp;
        }
      }
    }
    $response = [];
    for($i = 0; $i < count($groups); $i++){
      $resp['value'] = 0;
      $resp['group'] = $groups[$i]->name;
      for($j = 0; $j < count($homework); $j++){
        if($groups[$i]->name == $homework[$j]->group_id){
          $resp['value'] = $resp['value'] + $homework[$j]->value;
        }
      }
      array_push($response, $resp);
    } 
    return response()->json($response, 200);
  }

  public function addNewData(Request $req){
    $data = $req->all();
    // print_r(gettype(json_decode($data['data'], true)));
    // die();
    $tmp = $data['data'];
    $homeworks = json_decode(json_encode($tmp), true);
    $lesson_id = $data['lesson_id'];
    
    for($i = 0; $i < count($homeworks); $i++){
      $group = $homeworks[$i]['group'];
      $value = $homeworks[$i]['value'];
      $create_date = time();
      $homework = DB::select("SELECT * FROM `homework` WHERE `group_id` = '$group' AND `lesson_id` = '$lesson_id'");
      if(count($homework) > 0){
        $query = DB::connection()->getPdo()->exec("UPDATE `homework` SET `value`='$value', `timestamp` = '$create_date' WHERE `group_id` = '$group' AND `lesson_id` = '$lesson_id'");
      }else{
        $query = DB::connection()->getPdo()->exec(
          "INSERT 
          INTO `homework` (`value`, `group_id`, `lesson_id`, `timestamp`) 
          VALUES ('$value', '$group', '$lesson_id', '$create_date')"
        );
      }
    }
    
    $response['message']= 'Успешно';
    return response()->json($response, 200);
  }
}