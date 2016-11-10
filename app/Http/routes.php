<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});


$app->group(['middleware' => 'login' , 'namespace' => 'App\Http\Controllers'], function () use ($app) {

    //===============================分割线===============================//

    //用户个人信息展示
    $app->get('user/{id}', 'UserController@show');

    //用户发布内容feed
    $app->get('user/{id}/timeline', 'UserController@timeline');

    //用户关注信息feed
    $app->get('user/{id}/feed', 'UserController@feed');

    //用户关注用户
    $app->post('follow', 'UserController@follow');

    //用户取关用户
    $app->post('unfollow', 'UserController@unfollow');

    //===============================分割线===============================//

    //用户发布喧喧
    $app->post('noisy/create', 'NoisyController@create');

    //用户删除喧喧
    $app->post('noisy/delete', 'NoisyController@delete');

});