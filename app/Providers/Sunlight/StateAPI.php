<?php

namespace App\Providers\Sunlight;

use Log;
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

	//csv and api data
	//csv: leg_id,full_name,first_name,middle_name,last_name,suffixes,nickname,active,state,chamber,district,party,transparencydata_id,photo_url,created_at,updated_at
	const rename = [
		'boundary_id' => 'division',
		'full_name' => 'name',
		'photo_url' => 'photo',
		'url' => 'website',
	];

	const copy = [
		'address',
		'first_name',
		'middle_name',
		'last_name',
		'nickname',
		'suffixes',
		'district',
		'state',
		'votesmart_id',
		'party',
		'email',
		'leg_id',
		'transparencydata_id',
		'chamber',
		'offices'
	];

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

		$fields = self::rename;
		foreach(self::copy as $key){
			$fields[$key] = $key;
		}
		$this->fields = $fields;
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
				return $this->validate(json_decode($res->getBody(), true));
			},
			function(RequestException $e){
				return [];
			}
		);
	}

	public function divisions($lat, $lng)
	{
		return $this->client->getAsync('legislators/geo/?lat='.$lat.'&long='.$lng.'&fields=boundary_id')->then(
			function(ResponseInterface $res){
				$data = json_decode($res->getBody());
				foreach($data as $d){
					$d->division = $d->boundary_id;
					unset($d->boundary_id);
					unset($d->id);
				}
				return $data;
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
	 * convert Open States data to ContactMyReps data format
	 * @param  array $array Open States API response
	 * @return array        validated representatives
	 */
	public function validate($array)
	{
		$c = count($array);
		for ($i = 0; $i < $c; $i++){
			$data = $array[$i];

			if (isset($data['active']) && $data['active'] === 'False'){
				unset($array[$i]);
				continue;
			}

			$valid = new \stdClass();
			foreach($this->fields as $key=>$value){
				if (isset($data[$key])) $valid->$value = $data[$key];
			}

	    	$rep = Representative::find($valid);
	    	// if (is_null($rep)) $rep = new Representative($valid);
	    	if (is_null($rep)) continue; //for now no new reps

	    	if (in_array('openstates', $rep->sources)){
	    		$array[$i] = $rep;
	    		continue;
	    	}

	    	if (isset($data['chamber'])){
	    		if ($data['chamber'] == 'upper' || $data['chamber'] == 'senate'){
	    			$rep->title = 'State Senator';
	    			$rep->office = 'State Senate';
	    		}else if ($data['chamber'] == 'lower' || $data['chamber'] == 'house'){
	    			$rep->title = 'State Representative';
	    			$rep->office = 'State House';
	    		}
	    	}

			if (isset($data['offices']) && count($data['offices']) > 0){

				$office = array_first($data['offices'], function($key, $value){
					return $key == 'type' && $value == 'capitol';
				}, $data['offices'][0]);

				foreach($office as $k=>$v){
					if ( ! empty($rep->$k) || empty($v))
						continue;
					switch($k){
						case 'fax';
						case 'phone';
						case 'address';
						case 'email';
							$rep->$k = $v;
						break;
					}
				}
			}

			$rep->addSource('openstates');
			$rep->save();
	    	$array[$i] = $rep;
		}

		return array_values($array);
	}

    /**
     * download csv data from http://openstates.org/downloads/
     */
    public static function download()
    {
    	Log::info('downloading state api data');
		$data_path = "resources/assets/data/";
		libxml_use_internal_errors(true);
		$ht = file_get_contents("http://openstates.org/downloads/");
		$doc = new DOMDocument();
		$doc->loadHTML($ht);
		$x = new DOMXpath($doc);

		$table = $x->query('//table[@id="download_list"]')[0];
		$rows = $x->query('.//tr', $table);

		//state, json, csv - first row is header
		// 51 total for district of columbia
		for ($i = 1; $i <= 52; $i++){
			$state = $x->query('.//td[1]', $rows->item($i))[0];
			$state = $state->textContent;

			$zip_path = $data_path."temp/zips/".$state.".zip";
			$unzip_path = $data_path."states/".$state;

			$url = $x->query('.//td[3]/a', $rows->item($i))[0];
			$url = $url->getAttribute('href');

			$file = file_get_contents($url);
			if ($file === FALSE){
				Log::error("failed to download $state data at $url");
			}

			$status = file_put_contents($zip_path, $file);
			if ($status === FALSE){
				Log::error("failed to write temp data to $zip_path");
				continue;
			}

			$zip = new \ZipArchive;
			$res = $zip->open($zip_path);
			if ($res !== TRUE){
				Log::error("failed to open zip at $path");
			}

			$zip->extractTo($unzip_path);
			$zip->close();
		}
 		Log::info('finished downloading state api data');
    }


}