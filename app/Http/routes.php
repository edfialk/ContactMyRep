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

Route::get('/', 'RepresentativeController@viewIndex');
Route::get('/{zipcode}', 'RepresentativeController@viewZipcode');
Route::get('/{state}/{district}', 'RepresentativeController@viewDistrict');
Route::get('/{lat}/{lng}', 'RepresentativeController@viewGPS');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@jsonZipcode');
		Route::get('/{state}/{district}', 'RepresentativeController@jsonDistrict');
		Route::get('/{lat}/{lng}', 'RepresentativeController@jsonGPS');
	});
});
