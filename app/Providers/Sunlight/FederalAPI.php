<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;

use InvalidArgumentException;

/**
*
*/
class FederalAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	/**
	 * Return API locate data for zipcode
	 * @param  string $zip 5 or 9 digit zip code
	 * @return array        OpenCongress API response
	 */
	public function zip($zip)
	{
		if (!preg_match('/^\d{5}([\-]?\d{4})?$/', $zip)){
			throw new InvalidArgumentException('Invalid Zip Code');
		}

		$resp = $this->client->get('/legislators/locate?zip='.$zip);
		$json = json_decode($resp->getBody());
		return $json->results;
	}

	/**
	 * Return API data for single district
	 * @param  string $state    2 digit state abbreviation
	 * @param  string $district district number
	 * @return array            OpenCongress API response
	 */
	public function district($state, $district)
	{
		$state = strtoupper($state);
		$resp = $this->client->get('/legislators?state='.$state);
		$json = json_decode($resp->getBody());
		$results = $json->results;
		//since requesting by district doesn't return senators, request all for state and remove non-district house
		$c = count($results);
		for($i = 0; $i < $c; $i++){
			if (!empty($results[$i]->district) && $results[$i]->district != $district){
				unset($results[$i]); //unset keeps index
			}
		}
		return array_values($results); //fix index
	}

	public function gps($lat, $lng)
	{
		$resp = $this->client->get('/legislators/locate?latitude='.$lat.'&longitude='.$lng);
		$json = json_decode($resp->getBody());
		return $json->results;
	}

}