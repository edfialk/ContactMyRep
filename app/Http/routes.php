<?php

use App\Representative;
use App\Location;

// Authentication routes...
Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');

// Registration routes...
// Route::get('register', 'Auth\AuthController@getRegister');
// Route::post('register', 'Auth\AuthController@postRegister');

// Authenticated routes...
Route::get('edit/{id}', ['middleware' => 'auth', 'uses' => 'RepresentativeController@edit']);
Route::post('edit/{id}', ['middleware' => 'auth', 'uses' => 'RepresentativeController@store']);

//Page routes...
Route::get('', 'RepresentativeController@index');
Route::get('{zipcode}', 'RepresentativeController@view');
Route::get('{query}', 'RepresentativeController@view');
Route::get('{lat}/{lng}', 'RepresentativeController@view');
Route::get('rep/{id}','RepresentativeController@show');

//Api...
Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{query}', 'RepresentativeController@query');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});

