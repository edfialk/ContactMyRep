<?php

use Illuminate\Http\Request;

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

Route::get('/', function () {
    return view('pages.home');
});

Route::get('/{zipcode}', 'RepresentativeController@viewZipcode');
Route::get('/{state}/{district}', 'RepresentativeController@viewDistrict');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@jsonZipcode');
		Route::get('/{state}/{district}', 'RepresentativeController@jsonDistrict');
	});
});

Route::get('/test', function(Request $request){
	// return $request->ips();
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = filter_input('INPUT_SERVER', 'REMOTE_ADDR');
    }
    return $ipaddress;
});