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
	        	return (object)[
	        		'status' => 'error',
	        		'message' => $e->getResponse()->getReasonPhrase()
	        	];
	        }
		);
	}

	public function address($address)
	{
		return $this->async('representatives?address='.urlencode($address));
	}

	public function district($state, $district)
	{
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/cd:'.$district));
	}

	public function validate($data)
	{

		if (!isset($data->offices)){
			return (object) ['status' => 'error', 'message' => 'No Results.'];
		}

		$keys = [
			'name',
			'first_name',
			'last_name',
			'photoUrl' => 'photo',
			'party'
		];

		$response = (object) ['reps' => []];

		if (isset($data->normalizedInput)){
			$l = $data->normalizedInput;
			$response->location = (object)array(
				'city' => ucwords($l->city),
				'state' => $l->state,
				'zip' => $l->zip
			);
		}

		foreach($data->offices as $office){
			if (!Representative::isValidOffice($office->name))
				continue;

			foreach($office->officialIndices as $i){
				$d = $data->officials[$i];

				$rep = new Representative([
					'division_id' => $office->divisionId,
					'office' => $office->name
				]);
				$rep->load($d, $keys);

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
					$rep->email = $d->emails[0];
				}

				if (isset($d->channels)){
					foreach($d->channels as $c){
						$key = strtolower($c->type).'_id';
						$rep->$key = $c->id;
					}
				}

				$response->reps[] = $rep;
			}
		}
		return $response;
	}

	public function geocode($string)
	{
		$string = urlencode($string);
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$string);
		return json_decode($json);
	}
}