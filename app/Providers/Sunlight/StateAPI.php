<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Representative;

/**
* Sunlight Foundation Open States API wrapper
* For more information see https://sunlightlabs.github.io/openstates-api
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

	/**
	 * create asynchronous request to open states api
	 * @param  string $url  api endpoint and any query params
	 * @return promise      request promise
	 */
	public function async($url)
	{
		return $this->client->getAsync($url)->then(
			function(ResponseInterface $res){
				return $this->validate(json_decode($res->getBody()));
			},
			function(RequestException $e){
				echo $e->getMessage();
			}
		);
	}

	/**
	 * query api by district
	 * @param  string  $state    2 letter state abbreviation
	 * @param  integer $district district number
	 * @return promise           request promise
	 */
	public function district($state, $district)
	{
		return $this->async('legislators?district='.$district.'&state='.$state);
	}

	/**
	 * query api by gps coordinates
	 * @param  float $lat   latitude
	 * @param  float $lng   longitude
	 * @return promise      request promise
	 */
	public function gps($lat, $lng)
	{
		return $this->async('legislators/geo/?lat='.$lat.'&long='.$lng);
	}

	/**
	 * convert api data to contact my reps data format
	 * @param  array $array Open States API response
	 * @return array        validated representatives
	 */
	public function validate($array)
	{
		$keys = [
			'full_name' => 'name',
			'district',
			'state',
			'boundary_id' => 'division_id',
			'email',
			'party',
			'photo_url' => 'photo',
			'url' => 'website'
		];

		return array_map(function($data) use ($keys){
			$rep = new Representative($data, $keys);

			if (isset($data->offices) && count($data->offices) > 0){
				$office = $data->offices[0];
				foreach($data->offices as $d){
					if ($d->type == 'capitol'){
						$office = $d;
						break;
					}
				}
				$rep->phone = $office->phone ?? null;
				$rep->fax = $office->fax ?? null;
				$rep->address = $office->address ?? null;
				$rep->email = $office->email ?? null;
			}

	    	if (isset($data->chamber)){
	    		if ($data->chamber == 'upper' || $data->chamber == 'senate'){
	    			$rep->title = 'State Senator';
	    			$rep->office = 'State Senate';
	    		}else if ($data->chamber == 'lower' || $data->chamber == 'house'){
	    			$rep->title = 'State Representative';
	    			$rep->office = 'State House';
	    		}
	    	}

			return $rep;
		}, $array);
	}

}