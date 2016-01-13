<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

use App\Representative;

use InvalidArgumentException;

/**
*
*/
class CongressAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com/',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	public function async($url)
	{
		return $this->client->getAsync($url)->then(
	        function(ResponseInterface $res){
	            return $this->validate(json_decode($res->getBody()));
	        },
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	public function zip($zip)
	{
		return $this->async('/legislators/locate?zip='.$zip);
	}

	public function gps($lat, $lng)
	{
		return $this->async('/legislators/locate?latitude='.$lat.'&longitude='.$lng);
	}

	public function address($address)
	{
		
	}

	public function district($state, $district)
	{
		return $this->async('/legislators?state='.$state)->then(
	        function($data) use ($district){
	        	$c = count($data);
	        	for($i = 0; $i < $c; $i++){
					if (!empty($data[$i]->district) && $data[$i]->district != $district){
						unset($data[$i]);
					}
	        	}
	        	return array_values($data);
	        },
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	public function validate($data)
	{
		$keys = [
			'contact_form',
			'district',
			'facebook_id',
			'first_name',
			'fax',
			'last_name',
			'ocd_id' => 'division_id',
			'office' => 'address',
			'party',
			'phone',
			'state',
			'state_name',
			'title',
			'twitter_id',
			'website',
			'youtube_id'
		];

		return array_map(function($data) use ($keys){
			$rep = new Representative();
			$rep->aliases($data);
			$rep->load($data, $keys);

	    	if (isset($data->chamber)){
	    		if ($data->chamber == 'upper' || $data->chamber == 'senate'){
	    			$rep->title = 'Senator';
	    			$rep->office = 'Senate';
	    		}else if ($data->chamber == 'lower' || $data->chamber == 'house'){
	    			$rep->title = 'Representative';
	    			$rep->office = 'House of Representatives';
	    		}
	    	}

	    	if (count($rep->aliases) > 0 && !isset($rep->name)){
	    		$rep->name = $rep->aliases[0];
	    	}
	    	return $rep;

		}, $data->results);
	}
}