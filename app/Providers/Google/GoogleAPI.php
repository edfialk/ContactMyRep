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

	public function async($query){
		$url = 'representatives?address='.$query.'&key='.$this->api_key;
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

	private function request($query){
		$url = 'representatives?address='.$query.'&key='.$this->api_key;
		$req = $this->client->get($url);
		//todo: error check
		$data = json_decode($req->getBody());
		return $data;
	}


	public function district($state, $district)
	{
		$state = strtolower($state);
		$resp = $this->request('ocd-division/country:us/state:'.$state.'/cd:'.$district);
		return $this->response($resp);
	}

	/**
	 * [districtAsync description]
	 * @param  string $state    2 digit state abbrev - must be LOWERCASE before request
	 * @param  [type] $district [description]
	 * @return [type]           [description]
	 */
	public function districtAsync($state, $district)
	{
		$state = strtolower($state);
		$url = 'representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/cd:'.$district).'?key='.$this->api_key;
		return $this->client->getAsync($url)->then(
	        function(ResponseInterface $res){
	            return $this->validate(json_decode($res->getBody()));
	        },
	        function (RequestException $e){
	            echo $e->getMessage();
	        }
		);
	}

	public function validate($data)
	{

		if (!isset($data->offices)){
			return (object) ['error' => (object) ['message' => 'No Results.']];
		}

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
				$rep = [
					'name' => $d->name,
					'office' => $office->name,
					'division_id' => $office->divisionId
				];
				$rep['office'] = str_replace(" of the United States", "", $rep['office']);
				$rep['office'] = str_replace("United States ", "", $rep['office']);
				if (stripos($rep['office'], 'House of Representatives') !== false){
					$rep['office'] = 'House of Representatives';
				}

				if (!in_array($rep['office'], Representative::ranks)){
					continue;
				}

				if (isset($d->photoUrl)){
					$rep['photo'] = $d->photoUrl;
				}
				if (isset($d->address)){
					$rep['address'] = (array) $d->address[0];
				}
				if (isset($d->party) && $d->party != 'Unknown'){
					$rep['party'] = $d->party;
				}
				if (isset($d->phones) && count($d->phones) == 1){
					$rep['phone'] = $d->phones[0];
					$rep['phone'] = str_replace('(', '', $rep['phone']);
					$rep['phone'] = str_replace(') ', '-', $rep['phone']);
				}
				if (isset($d->urls) && count($d->urls) == 1){
					$rep['website'] = $d->urls[0];
				}
				if (isset($d->emails) && count($d->emails) == 1){
					$rep['emails'] = $d->emails[0];
				}

				if (isset($d->channels)){
					foreach($d->channels as $c){
						$rep[strtolower($c->type).'_id'] = $c->id;
					}
				}

				array_push($response['reps'], $rep);
			}
		}
		return $response;
	}

}