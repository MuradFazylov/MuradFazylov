<?php

namespace App\Http\Controllers\Businessman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Storage;
use Response;
use File;

class PassportController extends Controller
{
  public function uploadPassport(Request $req){
    $filename = time().".".$req->file('img')->getClientOriginalExtension();
    $result = $req->file('img')->move(storage_path('app/private/passports/13/'), $filename);
    $respose = ["filename"=>$filename];
    return response()->json($respose, 201);
  }

  public function getPassport($filename){
    $path = storage_path('app/private/passports/13/' . $filename);

    if (!File::exists($path)) {
      abort(404);
    }

    // $file = File::get($path);
    // $type = File::mimeType($path);
    // $response = Response::make($file, 200);
    // $response->header("Content-Type", $type);
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;

    return $response;
  }
}