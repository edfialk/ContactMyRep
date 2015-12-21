<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;

/**
*
*/
abstract class AbstractRepository
{

	protected $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function getClient()
	{
		return $this->client;
	}
}