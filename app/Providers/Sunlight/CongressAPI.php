<?php

namespace App\Providers\Sunlight;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Representative;
use InvalidArgumentException;

/**
* Sunlight Foundation Open Congress API wrapper
* For more information see https://sunlightlabs.github.io/congress/
*/
class CongressAPI
{

	protected $client;

	const keys = [
		'firstname' => 'first_name',
		'lastname' => 'last_name',
		'middlename' => 'middle_name',
		'ocd_id' => 'division_id',
		'congress_office' => 'address',
		'webform' => 'contact_form',
	];

	public function __construct()
	{
		$this->api_key = env('SUNLIGHT_KEY', null);

		if (null === $this->api_key){
			abort(500, 'Missing Sunlight API key');
		}

		$this->client = new Client([
			'base_uri' => 'http://congress.api.sunlightfoundation.com/',
			'headers' => [
				'X-APIKEY' => $this->api_key
			]
		]);
	}

	/**
	 * create asynchronous request to Open Congress API
	 * @param  string $url api endpoint and any query params
	 * @return promise      request promise
	 */
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

	/**
	 * query api by zip
	 * @param  string $zip zipcode
	 * @return promise      request promise
	 */
	public function zip($zip)
	{
		return $this->async('/legislators/locate?zip='.$zip);
	}

	/**
	 * query api by gps
	 * @param  string $lat latitude
	 * @param  string $lng longitude
	 * @return promise      request promise
	 */
	public function gps($lat, $lng)
	{
		return $this->async('/legislators/locate?latitude='.$lat.'&longitude='.$lng);
	}

	/**
	 * query api by district
	 * @param  string $state    2 digit state abbreviation
	 * @param  number $district district number
	 * @return promise           request promise
	 */
	public function district($state, $district)
	{
		return $this->async('/legislators?state='.$state)->then(
	        function($data) use ($district){
	        	$c = count($data);
	        	for($i = 0; $i < $c; $i++){
					if (!empty($data[$i]->district) && $data[$i]->district != $district){
						unset($data[$i]);
					}
	        	}
	        	return array_values($data);
	        },
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	/**
	 * convert api data to contact my reps data format
	 * @param  array $data Congress API response
	 * @return array       validated representatives
	 */
	public static function validate($array)
	{

		$c = count($array);
		for ($i = 0; $i < $c; $i++){
			$data = $array[$i];

			if (isset($data['in_office']) && $data['in_office'] == 0){
				unset($array[$i]);
				continue;
			}

			if ($data['district'] === "Junior Seat" || $data['district'] == "Senior Seat"){
				$data['title'] = 'Senator';
				$data['office'] = 'Senate';
			}else{
				$data['title'] = 'Representative';
				$data['office'] = 'House of Representatives';
			}

			$array[$i] = array_rename($data, self::keys);
		}

		return array_values($array);
	}
}