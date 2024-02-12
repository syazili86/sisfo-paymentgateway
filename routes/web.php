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
/*
* - gunakan POST localhost/UBDPayment/public/briva untuk membuat VA BRI
* - gunakan DELETE localhost/UBDPayment/public/briva untuk menghapus VA BRI
* - gunakan PUT localhost/UBDPayment/public/briva untuk mengupdate VA BRI
* - gunakan GET localhost/UBDPayment/public/briva untuk mendapatkan info VA BRI

*/
$router->group(['prefix'=>'briva'],function() use ($router){
    $router->post('/','BrivaController@store');
    $router->put('/','BrivaController@update');
    $router->get('/','BrivaController@index');
    $router->delete('/','BrivaController@destroy');
    $router->get('/status','BrivaController@status');

/*
* DEPRECATED
*
*   $router->post('/create','BrivaController@createVA');
    $router->get('/get','BrivaController@getVA');
    $router->delete('/delete','BrivaController@deleteVA');
    $router->put('/update','BrivaController@updateStatusVA');
*/

    $router->get('/get/report','BrivaController@getReportVA');
    $router->get('/get/report/time','BrivaController@getReportTimeVA');


    $router->get('/cron','BrivaController@cron');
});

