<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('/', function () {
    return view('welcome');
});


Route::get('/images/{type}/{id}', "ResourceController@getImage");
// images/mobile_background/1
//Route::get('/images/{type}/{id}', "ResourceController@getImage");
//Route::get('/images/test', "ResourceController@test");
Route::get('/stock/quotes', "ResourceController@getQuotes");

Route::get('/stock/history', "ResourceController@getHistory");

Route::get('/applyToken', "ManagementPagesController@applyToken");

Route::post('/requestToken', "ManagementPagesController@genToken");


Route::auth();



