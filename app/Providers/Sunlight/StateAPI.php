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
	public static function validate($array)
	{
		$keys = [
			'full_name' => 'name',
			'first_name',
			'middle_name',
			'last_name',
			'suffixes',
			'nickname',
			'chamber',
			'district',
			'state',
			'boundary_id' => 'division_id',
			'email',
			'party',
			'photo_url' => 'photo',
			'url' => 'website'
		];

		$c = count($array);
		for ($i = 0; $i < $c; $i++){
			$data = (object) $array[$i];
			if ($data->active == "False"){
				unset($array[$i]);
				continue;
			}

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

			$array[$i] = $rep;
		}

		return $array;

	}

    /**
     * download csv data from http://openstates.org/downloads/
     */
    public static function download()
    {
    	Log::info('downloading state api data');
		libxml_use_internal_errors(true);
		$data_path = "resources/assets/data/";
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