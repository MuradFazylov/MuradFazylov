<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// header('Content-type: json/application');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods': 'GET, PUT, POST, DELETE');
// header('Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('users', 'User\UserController@users');
Route::get('users/{id}', 'User\UserController@usersById');

Route::post('login', 'Login\LoginController@login');