<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;

/**
* 
*/
class ClientFactory
{

	public function make(array $config)
	{
		$params = $this->getParameters($config);

		$client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com',
			'headers' => [
				'X-APIKEY' => $params['apikey']
			]
		]);

		return $client;
	}


	public function getParameters(array $config)
	{
		if (!array_key_exists('apikey', $config)){
			throw new InvalidArgumentException('The Sunlight client requires configuration.');
		}

		return array_only($config, ['apikey']);
	}

}