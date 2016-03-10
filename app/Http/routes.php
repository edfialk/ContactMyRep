<?php

use App\Representative;
use App\Location;

//Authenticated routes
Route::group(['middleware' => 'auth'], function(){
	Route::get('log-viewer', '\Arcanedev\LogViewer\Http\Controllers\LogViewerController@index');
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

//Api...
Route::group(['prefix' => 'api'], function(){
	Route::get('wiki/senators', 'WikiController@senators');
	Route::get('wiki/house', 'WikiController@house');
	Route::get('sync/openstates/{max}', 'SyncController@openstates');
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{query}', 'RepresentativeController@query');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
	Route::group(['prefix' => 'page'], function(){
		Route::get('about', 'PageController@about');
	});
});

//Page routes...
Route::get('about', 'PageController@index');
Route::get('contact', 'PageController@index');
Route::post('contact', 'ContactController@sendContactMessage');
Route::get('', 'PageController@index');
Route::get('{zipcode}', 'PageController@index');
Route::get('{query}', 'PageController@index');
Route::get('{lat}/{lng}', 'PageController@index');
Route::get('rep/{id}','RepresentativeController@show');
Route::get('rep/{id}/flag', 'RepresentativeController@flag')->name('flagrep');
