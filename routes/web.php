<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Str;
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

$router->group(
    [
        // 'middleware' => 'api',
        'prefix' => 'auth'
    ],
    function () use ($router) {
        $router->post('/login', 'AuthController@login');
        $router->get('/me', 'AuthController@getMe');
        $router->post('/logout', 'AuthController@logout');
        $router->post('/refresh', 'AuthController@refresh');
    }
);

$router->group(['prefix' => 'aula'], function () use ($router) {
    $router->get('/', 'AulaController@index');
    $router->post('/', 'AulaController@store');
    $router->get('/{id}', 'AulaController@show');
    $router->put('/{id}', 'AulaController@update');
    $router->delete('/{id}', 'AulaController@destroy');
});;

$router->group(['prefix' => 'mobil'], function () use ($router) {
    $router->get('/', 'MobilController@index');
    $router->post('/', 'MobilController@store');
    $router->get('/{id}', 'MobilController@show');
    $router->put('/{id}', 'MobilController@update');
    $router->delete('/{id}', 'MobilController@destroy');
});

$router->group(['prefix' => 'penyewaan'], function () use ($router) {
    $router->get('/', 'PenyewaanController@index');
    $router->post('/', 'PenyewaanController@store');
    $router->get('/{id}', 'PenyewaanController@show');
    $router->post('/{id}', 'PenyewaanController@update');
    $router->delete('/{id}', 'PenyewaanController@destroy');
});


