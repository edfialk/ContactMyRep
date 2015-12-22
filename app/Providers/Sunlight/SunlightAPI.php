<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;

/**
*
*/
class SunlightAPI
{

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Sunlight API key');
		}

		$this->fed_client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
		$this->state_client = new Client([
			'base_uri' => 'http://openstates.org/api/v1/',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	public function getRepsByZipCode($zip)
	{
		$resp = $this->fed_client->get('/legislators/locate?zip='.$zip);
		$json = json_decode($resp->getBody());
		return $json->results;
	}

	public function getDistrict($state, $district){
		$resp = $this->state_client->get('legislators', [
			'query' => [
				'district' => $district,
				'state' => $state
			]
		]);
		return json_decode($resp->getBody());
	}


}