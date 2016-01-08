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
Route::get('/{zipcode}', 'RepresentativeController@view');
Route::get('/{address}', 'RepresentativeController@view');
Route::get('/{state}/{district}', 'RepresentativeController@view');
Route::get('/{lat}/{lng}', 'RepresentativeController@view');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@jsonZipcode');
		Route::get('/{state}/{district}', 'RepresentativeController@jsonDistrict');
		Route::get('/{lat}/{lng}', 'RepresentativeController@jsonGPS');
	});
});
