<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware'=>'cors'],function($router) {

//auth
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

//stuff
//struktur : $router->method('/path', 'NamaController@namafunction');
$router->get('/stuffs', 'StuffController@index');
$router->post('/stuffs/store', 'StuffController@store');
$router->get('/stuffs/trash', 'StuffController@trash');
$router->patch('/stuffs/update/{id}', 'StuffController@update');
$router->delete('/stuffs/delete/{id}', 'StuffController@destroy');
$router->get('/stuffs/trash/restore/{id}', 'StuffController@restore');

$router->get('/stuffs/trash/permanent-delete/{id}', 'StuffController@permanentDelete');

//inboundStuffs
$router->get('/inbound-stuffs/data', 'InboundStuffController@index');
$router->post('/inbound-stuffs/store', 'InboundStuffController@store'); 
$router->get('/inbound-stuffs/trash', 'InboundStuffController@trash');
$router->get('/restore/{id}', 'InboundStuffController@restore');

$router->delete('/inbound-stuffs/delete/{id}', 'InboundStuffController@destroy');
$router->delete('/inbound-stuffs/permanent/{id}', 'InboundStuffController@deletePermanent');

//lendings
$router->post('/lendings/store', 'LendingController@store');
$router->get('/lendings', 'LendingController@index');
$router->delete('/lendings/delete/{id}', 'LendingController@destroy');

//user
$router->get('/users/trash', 'UserController@trash');
$router->get('/users/trash/restore/{id}', 'UserController@restore');
$router->get('/users/trash/permanent-delete/{id}', 'UserController@permanentDelete');

$router->get('/users/{id}', 'UserController@show');
$router->patch('/users/update/{id}', 'UserController@update');
$router->delete('/users/delete/{id}', 'UserController@destroy');
$router->get('/users', 'UserController@index');
$router->post('/users/store', 'UserController@store');


//buat data restorations (pengembalian) menggunakan params data lnding_id agar data pengembalian dibuat berdasarkan data peminjamannya
$router->post('/restorations/{lending_id}', 'RestorationController@store');
});