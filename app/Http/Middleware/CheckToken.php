<?php

namespace App\Http\Middleware;

use Closure;

class CheckToken
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    
    $nonAuth = $request->getPathInfo();
    switch($nonAuth){
      case "/api/login":
        return $next($request);
    }

    $token = $request->header('Token');
    if(isset($token)){
      if(strlen($token) == 65){
        $response['message'] = 'Недействительный ключ';
        return response()->json($response, 404);
        
      }
      $tmp = $this->checkToken($token);
    }
    $time = time();
    if(isset($tmp)){
      if(($tmp->time - $time) < 3600 * 24){
        return $next($request);
      }else{
        $response['message'] = 'Срок ключа окончен';
        return response()->json($response, 401);
      }
    }
    $response['message'] = 'Недействительный ключ';
    return response()->json($response, 401);
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

  public function checkToken($token){
    $ENCRYPTION_KEY = "e0b4a607e5acf479fca0c337ca6172e80e13db1c";
    $decrypted = $this->decrypt($token, $ENCRYPTION_KEY);

    $response['decrypted'] = json_decode($decrypted);
    return json_decode($decrypted);
  }
}
