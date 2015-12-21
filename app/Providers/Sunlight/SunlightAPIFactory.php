<?php

namespace App\Providers\Sunlight;

use App\Providers\Sunlight\ClientFactory;
use App\Providers\Sunlight\LegislatorRepository;

class SunlightAPIFactory
{

	protected $client;

	public function __construct(ClientFactory $client)
	{
		$this->client = $client;
	}

	public function make(array $config)
	{
		$client = $this->client->make($config);
		$legislator = new LegislatorRepository($client);
		return new SunlightAPI($legislator)
	}

	public function getClient()
	{
		return $this->client;
	}

}