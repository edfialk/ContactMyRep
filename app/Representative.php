<?php

namespace App;

use App\Repositories\RepRepository;
use Jenssegers\Mongodb\Model as Eloquent;

class Representative extends Eloquent
{

	protected $collection = 'reps';

	const ranks = [
	    'Senate',
	    'House of Representatives',
	    'State Senate',
	    'State House',
	    'Mayor',
	    'Governor',
	    'President'
	];

	const aliases = [
		['nickname','last_name'],
		['nickname','middle_name','last_name'],
		['nickname','middle_name','last_name','name_suffix'],
		['first_name','last_name'],
		['first_name','middle_name','last_name'],
		['first_name','middle_name','last_name','name_suffix']
	];

    public function __construct($data = [], $keys = null)
    {
    	parent::__construct();
    	$this->load($data, $keys);
    	$this->setAliases();
    }

    public function load($data, $keys = null)
    {
    	if (is_null($keys)){
	    	foreach($data as $key=>$value){
   				$this->$key = $value;
	    	}
	    }else{
			foreach($keys as $key=>$value){
				if (is_string($key) && isset($data->$key))
					$this->$value = $data->$key;
				else if (isset($data->$value))
					$this->$value = $data->$value;
			}
	    }
    }

    public function isIn(array $data)
    {

        if (isset($this->name)){
	        $aliases = array_map(function($i){
	            return $i->aliases ?? [$i->name];
	        }, $data);
	        $c = count($aliases);
	        for ($i = 0; $i < $c; $i++){
	        	if (array_search($this->name, $aliases[$i]) !== false){
	        		return $i;
	        	}
	        }
	    }

	    return $this->search([
	    	'website', 'twitter_id', 'facebook_id'
	    ], $data);
    }

    public function search(array $keys, $data)
    {
    	foreach($keys as $k){
    		if (!isset($this->$k)) continue;
	        $haystack = array_map(function($i) use ($k){
	        	return isset($i->$k) ? strtolower($i->$k) : null;
	        }, $data);
	        if (($i = array_search(strtolower($this->$k), $haystack)) !== false){
	        	return $i;
	        }
    	}
    	return false;
    }

    public function setAliases()
    {
		$results = [];
		foreach(self::aliases as $a){
			$parts = [];
			foreach($a as $key){
				if (empty($this->$key)){
					continue 2;
				}
				array_push($parts, $this->$key);
			}
			$results[] = implode(" ", $parts);
		}
		if (count($results) == 1)
			$this->name = $results[0];
		if (count($results) > 1){
			$this->name = $results[0];
			$this->aliases = $results;
		}
    }

	public function loadAliases($data)
	{

		$results = [];
		foreach(self::aliases as $a){
			$parts = [];
			foreach($a as $key){
				if (empty($data->$key)){
					continue 2;
				}
				array_push($parts, $data->$key);
			}
			$results[] = implode(" ", $parts);
		}
		$this->aliases = $results;
	}

	public function imgFileName()
	{
		$dir = '/images/reps/';
		$ext = '.jpg';
		if (isset($this->nickname) && isset($this->last_name)){
			return $dir.$this->last_name.'-'.$this->nickname.$ext;
		}else if (isset($this->first_name) && isset($this->last_name)){
			return $dir.$this->last_name.'-'.$this->first_name.$ext;
		}else if (isset($this->name) && stripos($this->name, " ")){
			$names = explode(" ", $this->name);
			$first = $names[0];
			$last = $names[count($names) - 1];
			if (count($names) > 2 && (stripos($last, 'jr') !== false || stripos($last, 'sr') !== false))
				$last = $names[count($names) - 2];
			return $dir.$last.'-'.$first.$ext;
		}
		return $dir.'fail.jpg';
	}

	public function isStateLevel()
	{
		return $this->office == 'Senate' || $this->office == 'Governor';
	}

	public function setStateAttribute($val)
	{
		$this->attributes['state'] = strtoupper($val);
	}

	public function setOfficeAttribute($name)
	{
		if (stripos($name, 'House of Representatives') !== false){
			$this->attributes['office'] = 'House of Representatives'; //remove district
		}
		$this->attributes['office'] = str_replace(["United States ", " of the United States"], "", $name);
	}

    public static function isValidOffice($office)
    {
    	$temp = new Representative(['office' => $office]);
    	return array_search($temp->office, self::ranks) !== false;
    }

}
