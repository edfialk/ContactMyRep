<?php

namespace App\Providers\Google;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

use App\Representative;

use InvalidArgumentException;

/**
*
*/
class GoogleAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('GOOGLE_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Google API key');
		}

		$this->client = new Client([
			'base_uri' => 'https://www.googleapis.com/civicinfo/v2/'
		]);
	}

	public function async($url){
		$url .= stripos($url, '?') !== false ? '&' : '?';
		$url .= 'key='.$this->api_key;
		return $this->client->getAsync($url)->then(
	        function(ResponseInterface $res){
	            return $this->validate(json_decode($res->getBody()));
	        },
	        function (RequestException $e){
	        	$b = json_decode($e->getResponse()->getBody());
	            return (object)array(
	            	'error' => $b->error->message
	            );
	        }
		);
	}

	public function zip($zip)
	{
		return $this->async('representatives?address='.$zip);
	}

	public function gps($lat, $lng)
	{
		return $this->async('representatives?address='.$lat.','.$lng);
	}

	public function validate($data)
	{

		if (!isset($data->offices)){
			return (object) ['status' => 'error', 'message' => 'No Results.'];
		}

		$keys = [
			'name',
			'photoUrl' => 'photo',
			'party'
		];

		$response = ['reps' => []];

		if (isset($data->normalizedInput)){
			$l = $data->normalizedInput;
			$response['location'] = (object)array(
				'city' => ucfirst($l->city),
				'state' => $l->state,
				'zip' => $l->zip
			);
		}

		foreach($data->offices as $office){
			foreach($office->officialIndices as $i){
				$d = $data->officials[$i];
				$rep = new Representative([
					'office' => $office->name,
					'division_id' => $office->divisionId
				]);
				foreach($keys as $key=>$val){
					if (is_string($key) && isset($d->$key)){
						$rep->$val = $d->$key;
					}else if (isset($d->$val)){
						$rep->$val = $d->$val;
					}
				}

				$rep->office = str_replace(" of the United States", "", $rep->office);
				$rep->office = str_replace("United States ", "", $rep->office);
				if (stripos($rep->office, 'House of Representatives') !== false){
					$rep->office = 'House of Representatives'; //remove district
				}

				if (!in_array($rep->office, Representative::ranks)){
					continue;
				}

				if (isset($d->address) && count($d->address) == 1){
					$rep->address = $d->address[0];
				}
				if (isset($d->phones) && count($d->phones) == 1){
					$rep->phone = str_replace(['(', ') '], ['', '-'], $d->phones[0]);
				}
				if (isset($d->urls) && count($d->urls) == 1){
					$rep->website = $d->urls[0];
				}
				if (isset($d->emails) && count($d->emails) == 1){
					$rep->emails = $d->emails[0];
				}

				if (isset($d->channels)){
					foreach($d->channels as $c){
						$key = strtolower($c->type).'_id';
						$rep->$key = $c->id;
					}
				}

				$response['reps'][] = $rep;
			}
		}
		return $response;
	}

}