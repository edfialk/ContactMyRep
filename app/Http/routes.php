<?php

use App\Representative;
use App\Location;


//Authenticated routes
Route::group(['middleware' => 'auth'], function(){
	Route::get('logs', '\Arcanedev\LogViewer\Http\Controllers\LogViewerController@index');
	Route::get('reports', 'AdminController@reports');
	Route::get('edit/{id}', 'RepresentativeController@edit');
	Route::post('edit/{id}', 'RepresentativeController@store')->name('editrep');
});

// Authentication routes...
Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');

// Registration routes...
// Route::get('register', 'Auth\AuthController@getRegister');
// Route::post('register', 'Auth\AuthController@postRegister');


//Page routes...
Route::get('', 'RepresentativeController@index');
Route::get('{zipcode}', 'RepresentativeController@view');
Route::get('{query}', 'RepresentativeController@view');
Route::get('{lat}/{lng}', 'RepresentativeController@view');
Route::get('rep/{id}','RepresentativeController@show');
Route::get('rep/{id}/flag', 'RepresentativeController@flag')->name('flagrep');

//Api...
Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{query}', 'RepresentativeController@query');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});
