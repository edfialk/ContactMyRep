<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

use InvalidArgumentException;

/**
*
*/
class CongressAPI
{

	protected $client;

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (is_null($this->api_key)){
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com/',
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
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	public function asyncLocate($query)
	{
		$url = '/legislators/locate?'.$query;
		return $this->async($url);
	}

	/**
	 * Return API data for single district
	 * @param  string $state    2 digit state abbreviation - must be CAPS before request
	 * @param  string $district district number
	 * @return array            OpenCongress API response
	 */
	public function district($state, $district)
	{
		$state = strtoupper($state);
		$resp = $this->client->get('/legislators?state='.$state);
		$json = json_decode($resp->getBody());
		$results = $json->results;
		//since requesting by district doesn't return senators, request all for state and remove non-district house
		$c = count($results);
		for($i = 0; $i < $c; $i++){
			if (!empty($results[$i]->district) && $results[$i]->district != $district){
				unset($results[$i]); //unset keeps index
			}
		}
		return array_values($results); //fix index
	}

	public function districtAsync($state, $district)
	{
		return $this->client->getAsync('/legislators?state='.$state)->then(
	        function(ResponseInterface $res) use ($district){
	        	$data = json_decode($res->getBody());
	        	$c = count($data->results);
	        	for($i = 0; $i < $c; $i++){
					if (!empty($data->results[$i]->district) && $data->results[$i]->district != $district){
						unset($data->results[$i]); //unset keeps index
					}
	        	}
	        	$data->results = array_values($data->results);
	            return $this->validate($data);
	        },
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	public function validate($data)
	{
		$data = $data->results;
		foreach($data as &$rep){
			$aliases = [
				['nickname','last_name'],
				['nickname','middle_name','last_name'],
				['nickname','middle_name','last_name','name_suffix'],
				['first_name','last_name'],
				['first_name','middle_name','last_name'],
				['first_name','middle_name','last_name','name_suffix']
			];
			$rep->aliases = [];
			foreach($aliases as $a){
				$parts = [];
				foreach($a as $key){
					if (!isset($rep->$key)){
						continue 2;
					}
					array_push($parts, $rep->$key);
				}
				array_push($rep->aliases, implode(" ", $parts));
			}

			$rep->name = $rep->nickname ?: $rep->first_name;
			if (isset($rep->middle_name)){
				$rep->name .= ' '.$rep->middle_name;
			}
			$rep->name .= ' ' .$rep->last_name;
			if (isset($rep->name_suffix)){
				$rep->name .= ' '.$rep->name_suffix;
			}

			if (isset($rep->office)){
				$rep->address = $rep->office;
				unset($rep->office);
			}
			if (isset($rep->ocd_id)){
				$rep->division_id = $rep->ocd_id;
				unset($rep->ocd_id);
			}
	    	if (isset($rep->chamber)){
	    		if ($rep->chamber == 'upper' || $rep->chamber == 'senate'){
	    			$rep->title = 'Senator';
	    			$rep->office = 'Senate';
	    		}else if ($rep->chamber == 'lower' || $rep->chamber == 'house'){
	    			$rep->title = 'Representative';
	    			$rep->office = 'House of Representatives';
	    		}
	    	}

			unset($rep->first_name);
			unset($rep->middle_name);
			unset($rep->last_name);
			unset($rep->name_suffix);
			unset($rep->gender);
			unset($rep->in_office);
			unset($rep->oc_email);
		}
		return $data;
	}

}