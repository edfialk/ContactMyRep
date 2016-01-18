<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
*/

Route::get('', 'RepresentativeController@index');
Route::get('{zipcode}', 'RepresentativeController@view');
Route::get('{address}', 'RepresentativeController@view');
Route::get('{lat}/{lng}', 'RepresentativeController@view');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{address}', 'RepresentativeController@address');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});


Route::get('test', 'WikiAPI@representatives');