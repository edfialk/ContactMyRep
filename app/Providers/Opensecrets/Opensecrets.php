<?php

namespace App\Providers\Opensecrets;

use GuzzleHttp\Client;

/**
*
*/
class Opensecrets
{

	public function __construct()
	{

		$this->api_key = env('OPENSECRETS_KEY', null);
		if (is_null($this->api_key)){
			abort(500, 'Missing OpenSecrets API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://www.opensecrets.org/api/'
		]);
	}

	public function get($method = null, $cid = null, $cycle = 2016)
	{
		//cid: 'N00007360'
		if (is_null($method)){
			abort(500, 'Missing method for OpenSecrets API call');
		}
		//check cid for some methods
		$resp = $this->client->get('', [
			'query' => [
				'method' => $method,
				'cid' => $cid,
				'cycle' => $cycle,
				'apikey' => $this->api_key

			]
		]);
		$data = $resp->getBody();
		$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		return $array;
	}

	public function getCandidate($cid = null){
		if (is_null($cid)){
			abort(500, 'Missing candidate id for OpenSecrets candidate request');
		}

		return $this->get('candSummary', $cid);
	}

}