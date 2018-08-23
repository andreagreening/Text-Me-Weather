<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/forecast', 'HomeController@forecastByZip')->name('forecast.byZip');
Route::get('/send', 'MessageController@sendMessage')->name('send');
Route::get('/search', 'HomeController@searchWeather')->name('search.weather');
Route::get('/getforecast', 'HomeController@getWeather')->name('get.weather');
Route::any('/twilio', 'HomeController@twilio')->name('twilio');
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('test', 'HomeController@test')->name('test');

