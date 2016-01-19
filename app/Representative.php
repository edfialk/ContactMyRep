<?php

namespace App;

use App\Repositories\RepRepository;

class Representative
{

	const ranks = [
	    'Senate',
	    'House of Representatives',
	    'State Senate',
	    'State House',
	    'Mayor',
	    'Governor',
	    'President'
	];

    public function __construct($data = [])
    {
    	foreach ($data as $key=>$value){
    		if (empty($value)) continue;

    		if ($key == 'state' && strlen($value) == 2) $value = strtoupper($value);
    		if ($key == 'office') $value = self::office($value);

			$this->$key = $value;
    	}
    }

    public function load($data, $keys = null)
    {
    	if (is_null($keys)){
	    	foreach($data as $key=>$value){
	    		if (empty($this->$key))
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
	            return $i->aliases ?? null;
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

	public function aliases($data)
	{
		$aliases = [
			['nickname','last_name'],
			['nickname','middle_name','last_name'],
			['nickname','middle_name','last_name','name_suffix'],
			['first_name','last_name'],
			['first_name','middle_name','last_name'],
			['first_name','middle_name','last_name','name_suffix']
		];
		$this->aliases = [];
		foreach($aliases as $a){
			$parts = [];
			foreach($a as $key){
				if (empty($data->$key)){
					continue 2;
				}
				array_push($parts, $data->$key);
			}
			$this->aliases[] = implode(" ", $parts);
		}
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

	public static function office($name)
	{
		if (stripos($name, 'House of Representatives') !== false){
			return 'House of Representatives'; //remove district
		}
		return str_replace(["United States ", " of the United States"], "", $name);
	}

    public static function isValidOffice($office)
    {
    	$office = self::office($office);
    	return array_search($office, self::ranks) !== false;
    }

}
