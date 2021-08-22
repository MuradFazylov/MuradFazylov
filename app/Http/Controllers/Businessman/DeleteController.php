<?php

namespace App\Http\Controllers\Businessman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class DeleteController extends Controller
{
  public function deleteBusinessmanById(Request $req){
    $data = $req->all();
    $id = $data['id'];
    // $amount = $data['amount'];
    // $reason_id = $data['reason_id'];
    // $lesson = $data['lesson'];
    $date_create = time();
    $users = DB::select("SELECT * FROM `businessman` WHERE `id` = '$id'"); 
    $group = $users[0]->group_id;
    $query = DB::connection()->getPdo()->exec("UPDATE `businessman` SET `status`='deleted' WHERE `id` = '$id'");
    $this->setDeletedData($req);
    $this->deleteFromActivityTable($req);
    if($query == 1){
      if($group > 0){
        $query = DB::connection()->getPdo()->exec(
          "UPDATE `group` SET `amount` = `amount`-1 WHERE `name`= '$group' AND `couching` = 13"
        );
      }
      $response['message'] = 'Пользователь удален';
      return response()->json($response, 200);
    }else{
      $response['message'] = 'Неуспешно';
      return response()->json($response, 200);
    }
  }

  public function setDeletedDetailById(Request $req){
    $one = $this->setDeletedData($req);
    $two = $this->deleteFromActivityTable($req);
    if($one == 1 && $two == 1){
      $response['message'] = 'Причина удаления указана';
      return response()->json($response, 201);
    }
  }

  public function setDeletedData($req){
    $data = $req->all();
    $id = $data['id'];
    $amount = $data['amount'];
    $reason_id = $data['reason_id'];
    $lesson = $data['lesson'];
    $date_create = time();
    $refund = DB::connection()->getPdo()->exec(
      "INSERT 
        INTO `deleted` (`user_id`, `amount`, `reason_id`, `lesson`, `date`) 
        VALUES ('$id', '$amount', '$reason_id', '$lesson', '$date_create')"
    );
    return 1;
  }

  public function deleteFromActivityTable($req){
    $data = $req->all();
    $id = $data['id'];
    $lesson = $data['lesson'];
    for($i = $lesson; $i <= 14; $i++){
      $lesson_name = 'lesson_'."$i";
      $query = DB::connection()->getPdo()->exec(
        "UPDATE `activity` SET $lesson_name = 'd'  WHERE `user_id`= '$id'"
      );
    }
    return 1;
  }

  public function getAllDeletedBusinessmen(){
    $users = DB::select("SELECT * FROM `businessman` WHERE `status` = 'deleted'");
    $reasons = DB::select("SELECT * FROM `reasons` WHERE `type` = 'delete'");
    $user_id = $users[0]->id;
    $deleted = DB::select("SELECT * FROM `deleted`");
    
    $data = [];
    for($i = 0; $i < count($users); $i++){
      $tmp = false;
      for($j = 0; $j < count($deleted); $j++){   
        if($users[$i]->id == $deleted[$j]->user_id){
          $resp_data['id'] = $deleted[$j]->user_id;
          $resp_data['name'] = $users[$i]->name;
          $resp_data['surname'] = $users[$i]->surname;
          $resp_data['status'] = 'Удален';
          $resp_data['reason'] = $this->getReasonById($reasons, $deleted[$j]->reason_id);
          $resp_data['amount'] = $deleted[$j]->amount;
          $resp_data['lesson'] = $deleted[$j]->lesson;
          $resp_data['phone'] = strval($users[$i]->telephone);
          array_push($data, $resp_data);
          $tmp = true;
        }
      }
      if($tmp == false){
        $resp_data['id'] = $users[$i]->id;
        $resp_data['name'] = $users[$i]->name;
        $resp_data['surname'] = $users[$i]->surname;
        $resp_data['status'] = 'Удален';
        $resp_data['reason'] = '-';
        $resp_data['amount'] = '-';
        $resp_data['lesson'] = '-';
        array_push($data, $resp_data);
      }
    }
    return response()->json($data, 200);
  }

  private function getReasonById($reasons, $reason_id){
    for($i = 0; $i < count($reasons); $i++){
      if($reasons[$i]->id == $reason_id){
        return $reasons[$i]->title;
      }
    }
  }

  public function getToken(){
    $ENCRYPTION_KEY = "e0b4a607e5acf479fca0c337ca6172e80e13db1c";
    $arr = array('role' => 1, 'time' => 1234567890);
    $txt = json_encode($arr);
    $encrypted = $this->encrypt($txt, $ENCRYPTION_KEY);
    // echo $encrypted.'<br>';
    $decrypted = $this->decrypt($encrypted, $ENCRYPTION_KEY);
    // echo $decrypted;
    $response['encrypted'] = $encrypted;
    $response['decrypted'] = json_decode($decrypted);
    return response()->json($response, 200);
  }

  public function encrypt($decrypted, $key) {
    $ekey = hash('SHA256', $key, true);
    srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
    if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $ekey, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
    return $iv_base64 . $encrypted;
  }
  
  public function decrypt($encrypted, $key) {
    $ekey = hash('SHA256', $key, true);
    $iv = base64_decode(substr($encrypted, 0, 22) . '==');
    $encrypted = substr($encrypted, 22);
    $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $ekey, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
    $hash = substr($decrypted, -32);
    $decrypted = substr($decrypted, 0, -32);
    if (md5($decrypted) != $hash) return false;
    return $decrypted;
  }

  public function checkToken(Request $req){
    $data = $req->all();
    $token = $data['token'];
    $ENCRYPTION_KEY = "e0b4a607e5acf479fca0c337ca6172e80e13db1c";
    $decrypted = $this->decrypt($token, $ENCRYPTION_KEY);
    $response['decrypted'] = json_decode($decrypted);
    return response()->json($response, 200);
  }
}
