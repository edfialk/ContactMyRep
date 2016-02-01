<?php

namespace App\Providers\Google;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Location;
use App\Representative;

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
	        	$msg = $e->getResponse();
	        	$msg = is_null($msg) ? "Google Error" : $msg->getReasonPhrase();
	        	return (object)[
	        		'status' => 'error',
	        		'message' => $msg
	        	];
	        }
		);
	}

	public function division($division)
	{
		return $this->async('representatives/'.urlencode($division));
	}

	public function state($state)
	{
		$state = strtolower($state);
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state));
	}
	public function cd($state, $cd)
	{
		$state = strtolower($state);
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/cd:'.$cd));
	}
	public function sldl($state, $sldl)
	{
		$state = strtolower($state);
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/sldl:'.$sldl));
	}

	public function sldu($state, $sldu)
	{
		$state = strtolower($state);
		return $this->async('representatives/'.urlencode('ocd-division/country:us/state:'.$state.'/sldu:'.$sldu));
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

	public function update(array $reps)
	{
		$divisions = [];
		foreach($reps as $rep){
			if ( ! empty($rep->division) && ! in_array($rep->division, $divisions))
				array_push($divisions, $rep->division);
		}
        $requests = array_map(function($division){
        	return GoogleAPI::division($division);
        }, $divisions);
        $results = Promise\unwrap($requests);
        return array_collapse(array_map(function($result){
        	return $result->reps;
        }, $results));
	}

	/**
	 * convert api data to contact my reps data format
	 * @param  object $data google response data
	 * @return object       contact my reps api response
	 */
	public function validate($data)
	{
		$response = (object) [
			'reps' => [],
			'divisions' => []
		];

		if (isset($data->normalizedInput)){
			$l = $data->normalizedInput;
			$response->location = (object) array(
				'city' => ucwords($l->city),
				'state' => $l->state,
				'zip' => $l->zip
			);
		}
		if (!isset($data->divisions)) return $response;

		foreach($data->divisions as $k=>$v){
			$response->divisions[] = $k;
		}

		if (!isset($data->offices)) return $response;

		foreach($data->offices as $office){
			if ( ! Representative::isValidOffice($office->name)){
				continue;
			}
			$divisionId = $office->divisionId;
			$l = divisions_split([$divisionId]);

			foreach($office->officialIndices as $i){
				$d = $data->officials[$i];

				$d->office = $office->name;
				$d->division = $divisionId;
				if (isset($l['state'])) $d->state = $l['state'];

				$rep = Representative::find($d);
				// if (is_null($rep)) $rep = new Representative((array) $d);
				if (is_null($rep)) continue; //for now no new reps

				if (in_array('google', $rep->sources)){
					$response->reps[] = $rep;
					continue;
				}

				if (empty($rep->name) && isset($d->name))
					$rep->name = $d->name;
				if (empty($rep->party) && isset($d->party))
					$rep->party = $d->party;
				if (empty($rep->photo) && isset($d->photoUrl))
					$rep->photo = $d->photoUrl;

				if (empty($rep->address) && isset($d->address)){
					$new = [];
					$a = $d->address;
					if (is_array($a)) $a = $a[0];
					if (!empty($a->line1)) $new[] = $a->line1;
					if (!empty($a->line2)) $new[] = $a->line2;
					if (!empty($a->line3)) $new[] = $a->line3;
					if (!empty($a->city) && !empty($a->state) && !empty($a->zip))
						$new[] = ucwords($a->city).', '.$a->state.' '.$a->zip;
					$rep->address = $new;
				}

				if (isset($d->phones) && count($d->phones) > 0){
					$phones = $rep->phones ?? [];
					foreach($d->phones as &$p){
						$p = str_replace(['(', ') '], ['', '-'], $p);
						if (!in_array($p, $phones)) array_push($phones, $p);
					}
					$rep->phones = $phones;
					if (!isset($rep->phone)) $rep->phone = $phones[0];
				}
				if (isset($d->urls) && count($d->urls) > 0){
					$urls = $rep->urls ?? [];
					foreach($d->urls as $u){
						if (!in_array($u, $urls)) array_push($urls, $u);
					}
					$rep->urls = $urls;
					if (!isset($rep->website)) $rep->website = $urls[0];
				}
				if (isset($d->emails) && count($d->emails) > 0){
					$emails = $rep->emails ?? [];
					foreach($d->emails as $e){
						if (!in_array($e, $emails)) array_push($emails, $e);
					}
					$rep->emails = $emails;
					if (!isset($rep->email)) $rep->email = $emails[0];
				}
				if (isset($d->channels)){
					foreach($d->channels as $c){
						$key = strtolower($c->type).'_id';
						if (!isset($rep->$key)) $rep->$key = $c->id;
					}
				}

				$rep->addSource('google');
				$rep->save();
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