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

Route::get('', 'RepresentativeController@index');
Route::get('{zipcode}', 'RepresentativeController@view');
Route::get('{zipcode}/{address}', 'RepresentativeController@view');
Route::get('{lat}/{lng}', 'RepresentativeController@view');

Route::group(['prefix' => 'api'], function(){
	Route::group(['prefix' => 'v1'], function(){
		Route::get('/{zipcode}', 'RepresentativeController@zipcode');
		Route::get('/{address}', 'RepresentativeController@address');
		Route::get('/{state}/{district}', 'RepresentativeController@district');
		Route::get('/{lat}/{lng}', 'RepresentativeController@gps');
	});
});


Route::get('test', function(){
	ini_set('user_agent', 'ContactMyReps/0.1 (https://contactmyreps.org/; developing@contactmyreps.org)');
	$ht = file_get_contents('https://en.wikipedia.org/wiki/Current_members_of_the_United_States_House_of_Representatives');
	$doc = new DOMDocument();
	$doc->loadHTML($ht);
	$x = new DOMXpath($doc);
    // $xml = @simplexml_load_string($ht, 'SimpleXMLElement', LIBXML_NOCDATA);
    $table = $x->query('//table[contains(@class, "sortable")]')[1];
    $rows = $x->query('.//tr[td]', $table);
    foreach($rows as $row){
    	$name = $x->query('.//td[4]//span[@class="sortkey"]', $row);
    	if (count($name) > 0){
    		$name = str_replace(", ", "-", $name[0]->textContent);
	    	$img = $x->query('.//img', $row)->item(0);
	    	$src = $img->getAttribute('src');
	    	File::put(public_path('images/reps/'.$name.'.jpg'), file_get_contents('http:'.$src));
    	}
    }


    // return json_decode(json_encode((array) $xml), 1); 

});