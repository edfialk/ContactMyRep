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

	/**
	 * Return API data for single district
	 * @param  string $state    2 digit state abbreviation - must be CAPS before request
	 * @param  string $district district number
	 * @return array            OpenCongress API response
	 */
	public function district($state, $district)
	{
		return $this->async('/legislators?state='.$state)->then(
	        function(ResponseInterface $res) use ($district){
	        	$data = json_decode($res->getBody());
	        	$c = count($data->results);
	        	for($i = 0; $i < $c; $i++){
					if (!empty($data->results[$i]->district) && $data->results[$i]->district != $district){
						unset($data->results[$i]); //unset keeps index
					}
	        	}
	        	$data->results = array_values($data->results);
	            return $this->validate($data);
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
			'fax',
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
			foreach($keys as $key=>$value){
				if (is_string($key) && isset($data->$key)){
					$rep->$value = $data->$key;
				}else if (isset($data->$value)){
					$rep->$value = $data->$value;
				}
			}

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