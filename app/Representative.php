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
    }

    /**
     * copy info from data - warning overwrites present info!
     * @param  array $data input
     * @param  array $keys string names of fields to copy from data.
     *                     If entry is a key=>value, data->key will be copied to this->value
     * @return null
     */
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
    	$this->setAliases();
    }

    /**
     * attempt to determine if this rep is present in data
     * @param  array   $data a validated api response
     * @return index if found or false if not found
     */
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

    /**
     * search keys of data to find a match for this
     * @param  array  $keys string fields to search
     * @param  array $data validated api response
     * @return index if found else false
     */
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

    /**
     * populate name aliases for this rep
     */
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

	/**
	 * check if representative is at state level
	 * @return boolean
	 */
	public function isStateLevel()
	{
		return $this->office == 'Senate' || $this->office == 'Governor';
	}

	/**
	 * ensure state abbreviation is always 2 digit caps
	 * @param string $val state abbreviation
	 */
	public function setStateAttribute($val)
	{
		if (strlen($val) == 2){
			$this->attributes['state'] = strtoupper($val);
		}else{
			$this->attributes['state_name'] = $val;
		}
	}

	/**
	 * ensure office is one of accepted values
	 * @param string $name office name
	 */
	public function setOfficeAttribute($name)
	{
		if (stripos($name, 'House of Representatives') !== false){
			$this->attributes['office'] = 'House of Representatives'; //remove district
		}
		$this->attributes['office'] = str_replace(["United States ", " of the United States"], "", $name);
	}

	/**
	 * check if supplied office is on our approved list
	 * @param  string  $office
	 * @return boolean
	 */
    public static function isValidOffice($office)
    {
    	$temp = new Representative(['office' => $office]);
    	return array_search($temp->office, self::ranks) !== false;
    }

}
