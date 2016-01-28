<?php

namespace App\Providers\Google;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Representative;
use InvalidArgumentException;

/**
* Google Civic Information API wrapper
* For more info see: https://developers.google.com/civic-information/docs/v2/
*/
class GoogleAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('GOOGLE_KEY', null);

		if (is_null($this->api_key))
			abort(500, 'Missing Google API key');

		$this->client = new Client([
			'base_uri' => 'https://www.googleapis.com/civicinfo/v2/'
		]);
	}

	/**
	 * create asynchronous request to google's civic information api
	 * @param  string $url  api endpoint and any query params
	 * @return promise      request promise
	 */
	public function async($url)
	{
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

	public function divisions($query)
	{
		$fields = 'divisions';
		return $this->async('representatives?address='.urlencode($query).'&fields='.urlencode($fields));
	}

	/**
	 * query api by address
	 * @param  string $address  query
	 * @return promise          request promise
	 */
	public function address($address)
	{
		$fields = 'divisions,normalizedInput,offices,officials';
		return $this->async('representatives?address='.urlencode($address).'&fields='.urlencode($fields));
	}

	/**
	 * query api by district
	 * @param  string  	$state     	2 letter state abbreviation
	 * @param  integer 	$district  	district number
	 * @return promise				request promise
	 */
	public function district($state, $district)
	{
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/cd:'.$district));
	}

	/**
	 * convert api data to contact my reps data format
	 * @param  object $data google response data
	 * @return object       contact my reps api response
	 */
	public function validate($data)
	{

		$keys = [
			'name',
			'first_name',
			'last_name',
			'photoUrl' => 'photo',
			'party'
		];

		$response = (object) [
			'reps' => [],
			'divisions' => []
		];

		if (isset($data->normalizedInput)){
			$l = $data->normalizedInput;
			$response->location = (object)array(
				'city' => ucwords($l->city),
				'state' => $l->state,
				'zip' => $l->zip
			);
		}

		foreach($data->divisions as $k=>$v){
			$response->divisions[] = $k;
		}

		if (!isset($data->offices)) return $response;

		foreach($data->offices as $office){
			if (!Representative::isValidOffice($office->name))
				continue;

			$divisionId = $office->divisionId;

			foreach($office->officialIndices as $i){
				$d = $data->officials[$i];

				$rep = Representative::fromData($d, $keys);

				$rep->office = $office->name;
				$rep->division = $office->divisionId;
				$rep->state = $l->state;

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

	/**
	 * geocode a string (address, zipcode, state, etc.)
	 * @param  string $string query
	 * @return object         google geolocate data
	 */
	public function geocode($string)
	{
		$string = urlencode($string);
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$string);
		return json_decode($json);
	}
}