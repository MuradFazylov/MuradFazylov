<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use QRCode;
use File;
use Response;

class AuthorizationController extends Controller
{
  public function create(Request $req){   
    $data = $req->all();
    $user_id = $data['user_id'];
    $login = $data['login'];
    $password = md5($data['password']);
    
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `passwords` (`user_id`, `login`, `password`) 
      VALUES ('$user_id', '$login', '$password')"
      );
    $respose['message'] = 'Успешно! Пароль добавлен';
    return response()->json($respose, 201);
  }

  public function login(Request $req){   
    $data = $req->all();
    $login = $data['login'];
    $password = md5($data['password']);
    $query = DB::select(
      "SELECT * FROM `passwords` WHERE `login` = '$login'"
    );
    // return $query;
    if(count($query) > 0){
      if($query[0]->password == $password){
        $response = $this->setToken($query[0]->user_id);
        // return $response;
        return response()->json($response, 200);
      }else{
        $response['message'] = 'Логин или пароль указан неверно';
        return response()->json($response, 404);
      }   
    }else{
      $response['message'] = 'Пользователь не найден';
      return response()->json($response, 404);
    }
  }

  public function newPassword(Request $req){   
    $data = $req->all();
    $login = $data['login'];
    $password = md5($data['password']);
    $users = DB::connection()->getPdo()->exec("UPDATE `passwords` SET `password`='$password' WHERE  `login`='$login'");
    $response['message'] = 'Успешно!';
    return response()->json($response, 200);
  }

  private function setToken($user_id){
    $user_id = $user_id;
    $user = DB::select(
      "SELECT * FROM `users` WHERE `id` = '$user_id'"
    );
    $tmp = DB::connection()->getPdo()->exec("UPDATE `authorization` SET `status`='destroyed' WHERE `user_id`='$user_id'");
    $token = $this->getToken($user[0]->role);
    $token = addslashes($token);
    $time = time();
    $status = 'active';
    $query = DB::connection()->getPdo()->exec(
      "INSERT 
      INTO `authorization` (`user_id`, `token`, `status`, `time`) 
      VALUES ('$user_id', '$token', '$status', '$time')"
      );
    $response['message'] = 'Успешно!';
    $response['token'] = $token;
    return $response;
    return response()->json($response, 200);
  }

  public function getToken($role){
    $ENCRYPTION_KEY = "e0b4a607e5acf479fca0c337ca6172e80e13db1c";
    $time = time();
    $arr = array('role' => $role, 'time' => $time);
    $txt = json_encode($arr);
    $encrypted = $this->encrypt($txt, $ENCRYPTION_KEY);
    return $encrypted;
  }

  public function encrypt($decrypted, $key) {
    $ekey = hash('SHA256', $key, true);
    srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
    if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $ekey, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
    return $iv_base64 . $encrypted;
  }

  public function getUserSessionsById($id){
    $query = DB::select("SELECT FROM_UNIXTIME(time ,'%a %b %d %H:%i:%s UTC %Y') as loginDate FROM `authorization` WHERE `user_id` = $id  ORDER BY `id` DESC");
    return response()->json($query, 200);
  }

  public function image(Request $req){
    $data = $req->all();
    $datas = $data['datas'];
    $name = $data['name'];
    $surname = $data['surname'];
    $id = $data['id'];
    $qr = $data['qr'];
    $group = $data['group'];
    $work = $data['work'];
    $group = 'GURUH №'.$group;
    // print_r($data);
    // die();
    for($i = 0; $i < 1; $i++){
      $image1 = public_path('templates/template_min.jpg');

      /*work with image 2 */
      $exploded = explode(',', $datas, 2); // limit to 2 parts, i.e: find the first comma
      $encoded = $exploded[1]; // pick up the 2nd part    
      $decoded = base64_decode($encoded);
      $image2 = imagecreatefromstring($decoded);
      imagepng($image2, "uploads/qr.png");
      $image2 = public_path('uploads/').'qr.png'; 
      list($width, $height) = getImageSize($image2);
      $newwidth = $width * 0.95;
      $newheight = $height * 0.95;
      $thumb = imagecreatetruecolor($newwidth, $newheight);
      $source = imagecreatefrompng($image2);
      imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
      imagepng($thumb, "uploads/qr.png");

      
      $image2 = public_path('uploads/').'qr.png';     
      list($width, $height) = getImageSize($image2);
      $image1 = imagecreatefromstring(file_get_contents($image1));
      $image2 = imagecreatefromstring(file_get_contents($image2));
  
      imagecopymerge($image1, $image2, 187, 250, 0, 0, $width, $height, 100);
  
      $blue = imagecolorallocate($image1, 1, 57, 132);
      $white = imagecolorallocate($image1, 255, 255, 255);
      $font = public_path('templates/').'BebasNeue-Regular.ttf';
      $font2 = public_path('templates/').'roboto-light-italic_[allfont.ru].ttf';
      $size = 40;
      $box1 = imageftbbox( $size, 0, $font, $name ); 
      $x1 = (534 - ($box1[2] - $box1[0])) / 2;
      $box2 = imageftbbox( $size, 0, $font, $surname); 
      $x2 = (534 - ($box2[2] - $box2[0])) / 2;
      $box3 = imageftbbox( $size+10, 0, $font, $group); 
      $x3 = (534 - ($box3[2] - $box3[0])) / 2;
      $box4 = imageftbbox( $size-20, 0, $font2, $work); 
      $x4 = (534 - ($box4[2] - $box4[0])) / 2;   
      imageTTFText( $image1, $size, 0, $x1, 485, $blue, $font, $name );
      imageTTFText( $image1, $size, 0, $x2, 535, $blue, $font, $surname );
      imageTTFText( $image1, $size+10, 0, $x3, 683, $white, $font, $group );
      imageTTFText( $image1, $size-20, 0, $x4, 585, $blue, $font2, $work );

      header('Content_Type: image/png');
      
      if (!File::exists(public_path("uploads/badges/".$data['group']))){
        File::makeDirectory(public_path("uploads/badges/".$data['group']));
      }/*else{
          dump("Folder Already Exist : ".$key);
      }*/
      
      imagepng($image1, "uploads/badges/".$data['group']."/".$qr.".png");      
    }
    $query = DB::connection()->getPdo()->exec(
      "UPDATE `businessman` SET `qr` = '$qr' WHERE `id`= $id
    ");
    
    $response['link'] = $qr.".png";
    return response()->json($response, 200);  
  }

  public function folder(Request $req){
    $data = $req->all();
    $datas = $data['datas'];
    $name = $data['name']." ".$data['surname'];
    $surname = $data['surname'];
    $id = $data['id'];
    $qr = $data['qr'];
    $group = $data['group'];
    $gr = $data['group'];
    $work = $data['work'];
    $group = 'GURUH №'.$group;
    // print_r($data);
    // die();
    for($i = 0; $i < 1; $i++){
      $tmp = 0;
      $image1 = public_path('templates/big.jpg');
      if($gr >= 30 ){
        $tmp = 293;
        $image1 = public_path('templates/small.jpg');
      }
      

      /*work with image 2 */
      $exploded = explode(',', $datas, 2); // limit to 2 parts, i.e: find the first comma
      $encoded = $exploded[1]; // pick up the 2nd part    
      $decoded = base64_decode($encoded);
      $image2 = imagecreatefromstring($decoded);
      imagepng($image2, "uploads/qr.png");
      $image2 = public_path('uploads/').'qr.png'; 
      list($width, $height) = getImageSize($image2);
      $newwidth = $width * 1.05;
      $newheight = $height * 1.05;
      $thumb = imagecreatetruecolor($newwidth, $newheight);
      $source = imagecreatefrompng($image2);
      imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
      imagepng($thumb, "uploads/qr.png");

      
      $image2 = public_path('uploads/').'qr.png';     
      list($width, $height) = getImageSize($image2);
      $image1 = imagecreatefromstring(file_get_contents($image1));
      $image2 = imagecreatefromstring(file_get_contents($image2));
  
      imagecopymerge($image1, $image2, 2466, 332-$tmp, 0, 0, $width, $height, 100);
  
      $blue = imagecolorallocate($image1, 1, 57, 132);
      $white = imagecolorallocate($image1, 255, 255, 255);
      $font = public_path('templates/').'8483.ttf';
      $font2 = public_path('templates/').'roboto-light-italic_[allfont.ru].ttf';
      $size = 80;
      $box1 = imageftbbox( $size, 0, $font, $name ); 
      // $x1 = (534 - ($box1[2] - $box1[0])) / 2;
      imageTTFText( $image1, $size, 0, 500, 400-$tmp, $blue, $font, $name );

      header('Content_Type: image/png');
      
      if (!File::exists(public_path("uploads/folders/".$data['group']))){
        File::makeDirectory(public_path("uploads/folders/".$data['group']));
      }/*else{
          dump("Folder Already Exist : ".$key);
      }*/
      
      imagepng($image1, "uploads/folders/".$data['group']."/".$qr.".png");      
    }
    
    $response['link'] = $qr.".png";
    return response()->json($response, 200);  
  }

  public function zip($id){
    $zip = new ZipArchive;
    $fileName = $id.'.zip';
    if($zip->open(public_path($fileName),ZipArchive::CREATE) === TRUE){
      $files = File::files(public_path('uploads/badges/'.$id));
      foreach($files as $key => $value){
        $relativeNameInZipFile = basename($value);
        $zip->addFile($value,$relativeNameInZipFile);
      }
      $zip->close();
    }
  }

}
