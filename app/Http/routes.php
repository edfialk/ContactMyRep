<?php

use App\Representative;
use App\Location;

Route::get('test', function(){
	$reps = Representative::all();
	foreach($reps as $rep){
		$address = $rep->address;
		if (empty($address)) continue;
		if (is_object($address)){
			dd('obj', $address);
		}else if (is_array($address)){
			$new = [];
			if (!empty($address['line1']))
				array_push($new, $address['line1']);
			if (!empty($address['line2']))
				array_push($new, $address['line2']);
			if (!empty($address['line3']))
				array_push($new, $address['line3']);
			if (!empty($address['city']) && isset($address['state']) && isset($address['zip']))
				array_push($new, ucwords($address['city']).', '.$address['state'].' '.$address['zip']);
			$rep->address = $new;
			$rep->save();
			echo "------".$rep->name."-----\n";
			print_r($new);
		}else if (is_string($address)){
			$rep->address = [$address];
			$rep->save();
		}
	}
});

// Authentication routes...
Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('register', 'Auth\AuthController@getRegister');
Route::post('register', 'Auth\AuthController@postRegister');

Route::get('', 'RepresentativeController@index');
Route::get('{zipcode}', 'RepresentativeController@view');
Route::get('{address}', 'RepresentativeController@view');
Route::get('{lat}/{lng}', 'RepresentativeController@view');
Route::get('rep/{id}','RepresentativeController@show');

Route::get('edit/{id}', ['middleware' => 'auth', 'uses' => 'RepresentativeController@edit']);
Route::post('edit/{id}', ['middleware' => 'auth', 'uses' => 'RepresentativeController@store']);

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{address}', 'RepresentativeController@address');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});

