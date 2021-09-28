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
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('AuthKey')->get('users', 'User\UserController@users');
Route::get('users/{id}', 'User\UserController@userById');
Route::post('userByToken', 'User\UserController@userByToken');
Route::post('user/token/destroy', 'User\UserController@destroyToken');
Route::get('users/phone/{id}', 'User\UserController@userByPhone');
Route::get('users/role/{id}', 'User\UserController@userByRole');
Route::post('user/create', 'User\UserController@create');
Route::post('user/edit', 'User\UserController@editUser');
Route::get('delete/{id}', 'User\UserController@deleteUser');
Route::get('user/block/{id}', 'User\UserController@blockUser');
Route::get('user/activate/{id}', 'User\UserController@activateUser');
Route::post('user/upload/avatar', 'User\UserController@uploadAvatar');

Route::get('login/allBusinessman', 'Login\LoginController@getAllBusinessman');
// Route::post('login', 'Login\LoginController@login');

Route::get('businessmen', 'Businessman\BusinessmanController@getBusinessmen');
Route::get('businessmen/{id}', 'Businessman\BusinessmanController@getBusinessmanById');
Route::get('businessmen/status/{status}', 'Businessman\BusinessmanController@getBusinessmenByStatus');
// Route::get('businessman/delete/{id}', 'Businessman\BusinessmanController@deleteBusinessmanById');
Route::post('businessman/delete', 'Businessman\DeleteController@deleteBusinessmanById');
Route::post('businessman/delete/detail', 'Businessman\DeleteController@setDeletedDetailById');
Route::get('businessman/get/deleted', 'Businessman\DeleteController@getAllDeletedBusinessmen');
Route::get('businessmen/group/{id}', 'Businessman\BusinessmanController@getBusinessmenByGroup');
Route::get('getAllQR', 'Businessman\BusinessmanController@getAllQR');
Route::get('businessman/deleted/{id}', 'Businessman\BusinessmanController@getDeletedById');
Route::get('businessman/regions', 'Businessman\BusinessmanController@getBusinessmenGroupByRegions');

Route::get('businessman/processing', 'Businessman\CreateController@activityProcessing');

Route::get('regions/{lang}', 'Businessman\BusinessmanController@regions');
Route::post('addBusinessman', 'Businessman\BusinessmanController@addBusinessman');
Route::post('businessman/create/group', 'Businessman\BusinessmanController@addBusinessmanGroup');
Route::post('upload/avatar', 'Businessman\BusinessmanController@uploadAvatar');

Route::post('upload/logo', 'Businessman\BusinessmanController@uploadLogo');
Route::post('businessman/edit', 'Businessman\BusinessmanController@updateBusinessman');
Route::get('businessman/block/{id}', 'Businessman\BusinessmanController@blockBusinessman');
Route::get('businessman/blacklist/{id}', 'Businessman\BusinessmanController@blacklistBusinessman');

Route::get('lessons/{couching}', 'Lesson\LessonController@lessons');
Route::post('lesson/create', 'Lesson\LessonController@create');
Route::delete('lesson/delete/{id}', 'Lesson\LessonController@delete');

Route::middleware('AuthKey')->get('couchings', 'Couching\CouchingController@couching');
Route::middleware('AuthKey')->get('setAll', 'Couching\CouchingController@setCouchingNumber');
Route::post('couching/create', 'Couching\CouchingController@create');
Route::get('couching/last', 'Couching\CouchingController@getLastCouching');

Route::get('secret/sessions/{id}', 'User\AuthorizationController@getUserSessionsById');
Route::post('secret/create', 'User\AuthorizationController@create');
Route::post('secret/newPassword', 'User\AuthorizationController@newPassword');
Route::post('login', 'User\AuthorizationController@login');

Route::post('image', 'User\AuthorizationController@image');
Route::post('businessmen/cards/{id}', 'User\AuthorizationController@getAllCardsByGroup');

Route::get('groups', 'Group\GroupController@getGroups');
Route::get('groups/mentor/{id}', 'Group\GroupController@getGroupsByMentor');
Route::get('groups/set/auto', 'Group\GroupController@setGroupsAuto');
Route::post('group/mentor/set', 'Group\GroupController@groupMentorSet');
Route::post('groups/create', 'Group\GroupController@create');

Route::post('visit', 'Visit\VisitController@visit');
Route::post('visits/lesson', 'Visit\VisitController@visits');
Route::get('visits/all', 'Visit\VisitController@visitsAll');
Route::get('visits/group/{id}', 'Visit\VisitController@visitsByGroup');
Route::get('visits/activity', 'Visit\VisitController@setActivityTable');
Route::get('visits/table', 'Visit\VisitController@createActivityTable');
Route::get('visits/data/{id}', 'Visit\VisitController@setActivityData');
Route::get('visits/table/{id}', 'Visit\VisitController@getActivityTableByGroup');
Route::get('visits/user/{id}', 'Visit\VisitController@getActivityTableByUserId');
Route::get('visits/lesson/{id}', 'Visit\VisitController@getLessonStatistic');
Route::get('statistic/{id}', 'Visit\VisitController@getStatisticByGroups');
Route::get('statistics/lessons', 'Visit\VisitController@getStatisticByLessons');
Route::get('visit/group/{id}', 'Visit\VisitController@getVisitsByGroupId');
Route::get('visit/mentor/{id}', 'Visit\VisitController@getVisitsByMentorId');


Route::post('folder', 'User\AuthorizationController@folder');

Route::get('download/{file_name}', 'File\DownloadController@downloadImage');
Route::get('cards/zip/{file_name}', 'File\DownloadController@downloadZip');
Route::get('zip/{file_name}', 'File\DownloadController@zipBadges');
Route::get('folders/zip/{file_name}', 'File\DownloadController@zipFolders');

Route::get('branches', 'Couching\BranchesController@getBranches');
Route::get('reasons/{type}', 'Couching\BranchesController@getReasons');
Route::post('reason/absence/id', 'Couching\BranchesController@setAbsence');

Route::get('deleted', 'Statistic\DeletedController@getAllDeletedBusinessmen');
Route::get('checkDeleted', 'Statistic\DeletedController@checkDeleted');

Route::get('token', 'Businessman\DeleteController@getToken');
Route::post('token/check', 'Businessman\DeleteController@checkToken');

Route::get('homework/all', 'Homework\HomeworkController@getAllHomework');
Route::get('homework/mentor/{id}', 'Homework\HomeworkController@getHomeworkByMentor');
Route::get('homework/group/{id}', 'Homework\HomeworkController@getHomeworkByGroup');
Route::get('homework/lesson/{id}', 'Homework\HomeworkController@getHomeworkByLesson');
Route::post('homework/add', 'Homework\HomeworkController@addNewData');

Route::get('image/passport/{id}', 'Businessman\PassportController@getPassport');
Route::post('upload/passport', 'Businessman\PassportController@uploadPassport');