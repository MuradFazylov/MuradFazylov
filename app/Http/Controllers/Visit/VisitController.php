<?php

namespace App\Http\Controllers\Visit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class VisitController extends Controller
{
  public function visit(Request $requst){
    $data = $requst->all();
    $qr = $data['qr'];
    $lesson_id = $data['lesson_id'];
    $lesson_date = $data['lesson_date'];
    $terminal = $data['terminal'];
    $part = $data['part'];

    $query = DB::select("SELECT * FROM `businessman` WHERE `qr` = $qr");
    if(count($query) == 0){
      $response['message'] = 'Пользователь не найден';
      return response()->json($response, 404);
    }
    $user = $query[0];
    if($user->status != 'blocked'){
      $response = $this->checkVisit($user, $lesson_date, $lesson_id, $terminal, $qr, $part);
      return response()->json($response, $response['status']);
    }
    else{
      $response['message'] = 'Пользователь заблокирован';
      return response()->json($response, 400);
    }
  }


  public function checkVisit($businessman, $lesson_date, $lesson_id, $terminal, $qr, $part){
    $query = DB::select(
      "SELECT * FROM `visits` 
      WHERE `businessman_id` = $businessman->id AND `lesson_id` = $lesson_id AND `part` = $part
    ");
    if(count($query) == 0){
      $date = time();
      $query = DB::connection()->getPdo()->exec(
        "INSERT 
        INTO `visits` (`businessman_id`, `group_id`, `lesson_id`, `date`, `terminal`, `qr`, `part`) 
        VALUES ('$businessman->id', '$businessman->group_id', '$lesson_id', '$date', '$terminal', '$qr', '$part')"
      );
      $response['status'] = 201;
      $response['message'] = 'Успешно';
      $response['user'] = $businessman;
      return $response;
    }/*else if(count($query) > 0){
      $date = time();
      if(($date - $query[count($query)-1]->date) > 7200){
        $query = DB::connection()->getPdo()->exec(
          "INSERT 
          INTO `visits` (`businessman_id`, `group_id`, `lesson_id`, `date`, `terminal`, `qr`) 
          VALUES ('$businessman->id', '$businessman->group_id', '$lesson_id', '$date', '$terminal', '$qr')"
        );
        $response['status'] = 201;
        $response['message'] = 'Успешно';
        $response['user'] = $businessman;
        return $response;
      }else{
        $response['status'] = 400;
        $response['user'] = $businessman;
        $response['message'] = 'Менее 2-х часов. По данному QR-коду пользователь сегодня проходил авторизацию в '.date("H:i:s", $query[0]->date+( 3600 * 5));
        return $response;
      }
    }*/else{
      $response['status'] = 400;
      $response['user'] = $businessman;
      $response['message'] = 'По данному QR-коду пользователь сегодня проходил авторизацию в '.date("H:i:s", $query[0]->date+( 3600 * 5));
      return $response;
    }
  }

  public function visits(Request $requst){
    $data = $requst->all();
    $part = $data['part'];
    $lesson_id = $data['lesson_id'];
    $users = DB::select("SELECT qr FROM `visits` WHERE `part` = '$part' AND `lesson_id` = '$lesson_id'");
    $resp = [];
    foreach ($users as $user) {
      if($user->qr != null)
      array_push($resp, $user->qr);
    }
    $response['data'] = $resp;
    return response()->json($response, 200);
  }

  public function visitsAll(){
    DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $users = DB::select("SELECT * FROM `visits` GROUP BY qr/* LIMIT 10*/");
    return response()->json($users, 200);
  }

  public function visitsByGroup($id){
    DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $users = DB::select("SELECT * FROM `visits` WHERE `group_id` = '$id' GROUP BY qr");
    return response()->json($users, 200);
  }

  public function createActivityTable(){
    
    $businessmen = DB::select("SELECT * FROM `businessman`");

    for($i = 0; $i < count($businessmen); $i++){
      $businessman = $businessmen[$i];
      $name = addslashes($businessman->name);
      $surname = addslashes($businessman->surname);
      $query = DB::connection()->getPdo()->exec(
        "INSERT 
        INTO `activity` (`name`, `surname`, `user_id`, `group_id`) 
        VALUES ('$name', '$surname', '$businessman->id', '$businessman->group_id')"
      );

      if($businessman->status == 'deleted'){
        $query = DB::connection()->getPdo()->exec(
          "UPDATE `activity` SET 
          `lesson_1` = 'd',
          `lesson_2` = 'd',
          `lesson_3` = 'd',
          `lesson_4` = 'd',
          `lesson_5` = 'd',
          `lesson_6` = 'd',
          `lesson_7` = 'd',
          `lesson_8` = 'd',
          `lesson_9` = 'd',
          `lesson_10` = 'd',
          `lesson_11` = 'd',
          `lesson_12` = 'd',
          `lesson_13` = 'd',
          `lesson_14` = 'd'
          WHERE `user_id`= '$businessman->id'
        ");
      }
    }
    $response['message'] = 'Таблица создана';
    return response()->json($response, 200); 
  }

  public function setActivityData($id){
    DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $visits = DB::select("SELECT * FROM `visits` WHERE `lesson_id` = '$id' GROUP BY qr");
    for($j = 0; $j < count($visits); $j++){    
      $visit = $visits[$j];
      $query = DB::connection()->getPdo()->exec(
        "UPDATE `activity` SET `lesson_$visit->lesson_id` = '1' WHERE `user_id`= $visit->businessman_id
      ");
    }
  }

  public function getActivityTable(){
    $table = DB::select("SELECT * FROM `activity`");
    $response['data'] = $table;
    return response()->json($response, 200);
  }

  public function getActivityTableByGroup($id){
    $table = DB::select("SELECT * FROM `activity` WHERE `group_id`= '$id'");
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching`= '3'");
    $lastLesson = $this->getLastLesson($lessons);
    

    for($i = 0; $i < count($table); $i++){
      for($j = 0; $j < count($lessons); $j++){
        $lesson = 'lesson_'.$lessons[$j]->lesson; 
        if($lessons[$j]->lesson > $lastLesson){
          $table[$i]->$lesson = '3';
        }
      }
    }

    $response['data'] = $table;
    $response['count'] = count($table);
    return response()->json($response, 200);
  }

  public function getActivityTableByUserId($id){
    $activity = DB::select("SELECT * FROM `activity` WHERE `user_id`= '$id'");
    $activity_id = $activity[0]->id;
    $absence = DB::select("SELECT * FROM `absence` WHERE `activity_id`= '$activity_id'");
    $reasons = DB::select("SELECT * FROM `reasons` WHERE `type`= 'absence'");
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching`= '3'");
    $lastLesson = $this->getLastLesson($lessons); 
    $data = [];

    for($i = 0; $i < count($lessons); $i++){
      $lesson = 'lesson_'.$lessons[$i]->lesson;
      if($activity[0]->$lesson == 1){
        $num = $i+1;
        $tmp_array_item['status'] = 1;
        $tmp_array_item['lesson'] = "Занятие $num";
        $tmp_array_item['reason'] = "-";
        array_push($data, $tmp_array_item);
      }
      if($activity[0]->$lesson == 0){
        if($lessons[$i]->lesson <= $lastLesson){
          $num = $i+1;
          $tmp_array_item['status'] = 0;
          $tmp_array_item['lesson'] = "Занятие $num";   
          $tmp_array_item['reason'] = $this->getReasonById($reasons, $absence, $lessons[$i]->lesson);
          array_push($data, $tmp_array_item);
        }
        if($lessons[$i]->lesson > $lastLesson){
          $num = $i+1;
          $tmp_array_item['status'] = 2;
          $tmp_array_item['lesson'] = "Занятие $num";   
          $tmp_array_item['reason'] = "Занятие еще не проведено";
          array_push($data, $tmp_array_item);
        }
      }
    }
    $response['data'] = $data;
    return response()->json($response, 200);
  }

  private function getLastLesson($lessons){
    $lastLesson = 0;
    for($i = 0; $i < count($lessons); $i++){
      if($lessons[$i]->status == 'completed'){
        $lastLesson++;
      }
    }
    return $lastLesson;
  }

  private function getReasonById($reasons, $absence, $lesson){
    for($i = 0; $i < count($reasons); $i++){
      for($j = 0; $j < count($absence); $j++){
        if($absence[$j]->lesson_id == $lesson){
          if($reasons[$i]->id == $absence[$j]->reason_id){
            return $reasons[$i]->title;
          }
        }
      }
    }
    return 'Неизвестная причина';
  }

  public function getStatisticByGroups($id){
    $this->setActivityData($id);
    $groups = DB::select("SELECT * FROM `group`");
    $data = [];
    $mentors = [];
    for($i = 0; $i < count($groups); $i++){
      $group = $groups[$i]->name;
      $table = DB::select("SELECT * FROM `activity` WHERE `group_id` = '$group' ");
      if(count($table) > 0){
        $was = 0;
        $not = 0;
        $del = 0;
        $tmp = 'lesson_'.$id;
        for($j = 0; $j < count($table); $j++){
          if($table[$j]->$tmp == '0'){
            $not++;
          }
          if($table[$j]->$tmp == '1'){
            $was++;
          }
          if($table[$j]->$tmp == 'd'){
            $del++;
          }
        }
        $res_data_item['group'] = $groups[$i]->name;
        $res_data_item['was'] = $was;
        $res_data_item['not'] = $not;
        $res_data_item['del'] = $del;
        array_push($data, $res_data_item);
      }
    }
    
    $instructors = DB::select("SELECT * FROM `users` WHERE `role` = '3'");
    for($i = 0; $i < count($instructors); $i++){
      // print_r($instructors[$i]->name." ".$instructors[$i]->surname);    
      $resp['name'] = $instructors[$i]->name{0}.". ".$instructors[$i]->surname;
      $resp['data'] = $this->countByIntructor($groups, $data, $instructors[$i]->id);
      array_push($mentors, $resp);
    }

    $all_in = 0;
    $all_out = 0;
    $online_in = 0;
    $online_out = 0;
    $offline_in = 0;
    $offline_out = 0;

    for($k = 0; $k < count($data); $k++){
      if($data[$k]['group'] < 30){
        $offline_in = $offline_in + $data[$k]['was'];
        $offline_out = $offline_out + $data[$k]['not'];
      }
      if($data[$k]['group'] >= 30){
        $online_in = $online_in + $data[$k]['was'];
        $online_out = $online_out + $data[$k]['not'];
      }
    }
    $all_in = $offline_in + $online_in;
    $all_out = $offline_out + $online_out;

    $res_data_it['all_in'] = $all_in;
    $res_data_it['all_out'] = $all_out;
    $res_data_it['online_in'] = $online_in;
    $res_data_it['online_out'] = $online_out;
    $res_data_it['offline_in'] = $offline_in;
    $res_data_it['offline_out'] = $offline_out;

    $response['groups'] = $data;
    $response['mentors'] = $mentors;
    $response['status'] = $res_data_it;
    $response['region'] = $this->getStatisticByregion($data, $groups);
    return response()->json($response, 200);
  }

  private function countByIntructor($groups, $data, $id){
    $was = 0;
    $not = 0;
    $del = 0;

    for($i = 0; $i < count($groups); $i++){ 
      if($groups[$i]->mentor == $id){
        for($j = 0; $j < count($data); $j++){   
          if($groups[$i]->name == $data[$j]['group']){
            $was = $was + $data[$j]['was'];
            $not = $not + $data[$j]['not'];
            $del = $del + $data[$j]['del'];
          }
        }
      }
    }
    $res_data_item['was'] = $was;
    $res_data_item['not'] = $not;
    $res_data_item['del'] = $del;
    return $res_data_item;
  }

  private function getStatisticByregion($data, $groups){
    $region = [];
    $regions = DB::select("SELECT * FROM `regions`");
    for($i = 0; $i < count($groups); $i++){
      if($groups[$i]->name >= 30){
        for($j = 0; $j < count($regions); $j++){
          if($groups[$i]->group_region == $regions[$j]->id){
            $res_data_reg['region'] = $regions[$j]->name_ru;   
            $res_data_reg['group'] = $groups[$i]->name;
            $res_data_reg['was'] = 0;
            $res_data_reg['not'] = 0;
            $res_data_reg['del'] = 0;
            array_push($region, $res_data_reg);
          } 
        }
      }
    }
    for($i = 0; $i < count($data); $i++){
      for($j = 0; $j < count($region); $j++){
        if($data[$i]['group'] == $region[$j]['group']){
          $region[$j]['was'] = $data[$i]['was'];
          $region[$j]['not'] = $data[$i]['not'];
          $region[$j]['del'] = $data[$i]['del'];
        }
      }
    }

    for($i = 0; $i < count($region); $i++){
      if($region[$i]['was'] == 0 && $region[$i]['not'] == 0 && $region[$i]['del'] == 0){
        array_splice($region, $i, 1);
      }
    }

    return $region;
  }

  public function getLessonStatistic($id){
    $lesson = 'lesson_'.$id;
    $table = DB::select("SELECT * FROM `activity` WHERE $lesson = '0'");
    $response['data'] = $table;
    $response['count'] = count($table);
    return response()->json($response, 200);
  }
  
  public function getStatisticByLessons(){
    DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $users1 = DB::select("SELECT * FROM `activity`");
    $active = [];
    for($i = 0; $i < 14; $i++){
      $res_data_reg['lesson'] = $i+1;
      $res_data_reg['value'] = 0;
      array_push($active, $res_data_reg);
    }

    for($i = 0; $i < 14; $i++){
      $tmp = $i + 1;
      $tmp = "lesson_".$tmp;
      for($j = 0; $j < count($users1); $j++){
        if($users1[$j]->$tmp == 1){
          $active[$i]['value']++;
        }
      }
    }

    return response()->json($active, 200);
  }

  public function getVisitsByGroupId($id){
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching` = 3");
    $activity = DB::select("SELECT * FROM `activity` WHERE `group_id` = $id");
    $response = [];
    
    for($i = 0; $i < count($lessons); $i++){
      $lesson = 'lesson_'.$lessons[$i]->lesson;
      $tmp['lesson'] = $lessons[$i]->lesson;
      $tmp['value'] = 0;
      for($j = 0; $j < count($activity); $j++){
        if($activity[$j]->$lesson == 1){
          $tmp['value']++;
        }
      }
      array_push($response, $tmp);
    }
    return response()->json($response, 200);
  }

  public function getVisitsByMentorId($id){
    $lessons = DB::select("SELECT * FROM `lessons` WHERE `couching` = 3");
    $activity = DB::select("SELECT * FROM `activity`");
    $mentor = DB::select("SELECT * FROM `users` WHERE `id` = $id");
    $groups = DB::select("SELECT * FROM `group` WHERE `mentor` = $id");
    $response = [];
    
    for($i = 0; $i < count($lessons); $i++){
      $lesson = 'lesson_'.$lessons[$i]->lesson;
      $tmp['lesson'] = $lessons[$i]->lesson;
      $tmp['value'] = 0;
      
      for($j = 0; $j < count($activity); $j++){
        
        for($k = 0; $k < count($groups); $k++){
          if($activity[$j]->group_id == $groups[$k]->name && $activity[$j]->$lesson == 1){
            $tmp['value']++;
          }
        }
      }
      array_push($response, $tmp);
    }
    return response()->json($response, 200);
  }

}
