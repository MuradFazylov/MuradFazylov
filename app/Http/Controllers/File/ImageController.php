<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Storage;
use Response;
use File;

class ImageController extends Controller
{
  public function getPassport($filename){
    $path = storage_path('app/private/passports/13/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
  }
}