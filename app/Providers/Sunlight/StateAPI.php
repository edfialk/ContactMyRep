<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

use App\Representative;
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

	public function district($state, $district)
	{
		return $this->async('legislators?district='.$district.'&state='.$state);
	}

	public function gps($lat, $lng)
	{
		return $this->async('legislators/geo/?lat='.$lat.'&long='.$lng);
	}

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
			$rep = new Representative();
			$rep->aliases($data);
			foreach($keys as $key=>$val){
				if (is_string($key) && isset($data->$key)){
					$rep->$val = $data->$key;
				}else if (isset($data->$val)){
					$rep->$val = $data->$val;
				}
			}

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