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

Route::get('/', 'RepresentativeController@index');
Route::get('/{zipcode}', 'RepresentativeController@view');
Route::get('/{address}', 'RepresentativeController@view');
Route::get('/{state}/{district}', 'RepresentativeController@view');
Route::get('/{lat}/{lng}', 'RepresentativeController@view');

Route::get('images/{key}', 'ImageController@show');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});
