<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;

/**
*
*/
class StateAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://openstates.org/api/v1/',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	public function district($state, $district)
	{
		$resp = $this->client->get('legislators', [
			'query' => [
				'district' => $district,
				'state' => $state
			]
		]);
		return json_decode($resp->getBody());
	}

	public function gps($lat, $lng)
	{
		$resp = $this->client->get('legislators/geo/', [
			'query' => [
				'lat' => $lat,
				'long' => $lng
			]
		]);
		return json_decode($resp->getBody());
	}

}