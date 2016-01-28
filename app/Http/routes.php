<?php

use App\Representative;
use App\Location;

Route::get('test', function(){

	$locs = Location::where('zip', 35223)->get();
	$res = [];
	foreach($locs as $loc){
		$reps = Representative::where('state', $loc->state)->get();
		$res = array_merge($res, $reps->toArray());
	}
	return $res;
	// return Representative::where('state','OR')->where('district','1')->get();

});

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