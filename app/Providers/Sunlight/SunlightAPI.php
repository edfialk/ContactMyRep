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
			// throw new InvalidArgumentException('Missing Sunlight API Key');
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	public function get()
	{
		$resp = $this->client->request('GET', '/legislators/locate?zip=97209');
		$data = json_decode($resp->getBody());
		return $data;
	}

	public function getRepsByZipCode($zip)
	{
		$resp = $this->client->request('GET', '/legislators/locate?zip='.$zip);
		$data = json_decode($resp->getBody());
		return $data;

	}


}