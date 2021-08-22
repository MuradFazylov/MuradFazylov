<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use File;
use Response;
use ZipArchive;

class DownloadController extends Controller
{
  public function downloadImage($file_name){
    $file_name = str_replace("_", "/", $file_name);
    $path = public_path('uploads/'.$file_name);
    $file = File::get($path);
    $response = Response::make($file, 200);
    $response->header('Content-Type', 'image/png');
    return $response;
  }

  public function downloadZip($file_name){
    $path = public_path($file_name);
    $file = File::get($path);  
    
    $response = Response::make($file, 200);
    $response->header('Content-Type', 'image/png');
    return $response;
  }

  public function zipBadges($id){
    $fileName = $id.'.zip';
    File::delete((public_path($fileName)));
    $zip = new ZipArchive;
    $fileName = $id.'.zip';
    if($zip->open(public_path($fileName),ZipArchive::CREATE) === TRUE){
      $files = File::files(public_path('uploads/badges/'.$id));
      foreach($files as $key => $value){
        $relativeNameInZipFile = basename($value);
        $zip->addFile($value,$relativeNameInZipFile);
      }
      $zip->close();
      $this->deleteDir(public_path('uploads/badges/'.$id));
      $response['message'] = 'Успешно';
      return response()->json($response, 200);
    }
  }

  public function zipFolders($id){
    $fileName = 'folders_'.$id.'.zip';
    File::delete((public_path($fileName)));
    $zip = new ZipArchive;
    $fileName = 'folders_'.$id.'.zip';
    if($zip->open(public_path($fileName),ZipArchive::CREATE) === TRUE){
      $files = File::files(public_path('uploads/folders/'.$id));
      foreach($files as $key => $value){
        $relativeNameInZipFile = basename($value);
        $zip->addFile($value,$relativeNameInZipFile);
      }
      $zip->close();
      $this->deleteDir(public_path('uploads/folders/'.$id));
      $response['message'] = 'Успешно';
      return response()->json($response, 200);
    }
  }

  public static function deleteDir($dirPath) {
    if(! is_dir($dirPath)) {
      throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if(substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
      if(is_dir($file)){
          self::deleteDir($file);
      }else {
        unlink($file);
      }
    }
    rmdir($dirPath);
  }
}
