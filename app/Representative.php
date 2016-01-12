<?php

namespace App;

use App\Repositories\RepRepository;

class Representative
{

	const ranks = [
	    'Senate',
	    'House of Representatives',
	    'Mayor',
	    'Governor',
	    'President'
	];

    public function __construct($data = [])
    {
    	foreach ($data as $key=>$value){
    		if (empty($value)) continue;

    		if ($key == 'state' && strlen($value) == 2) $value = strtoupper($value);

			$this->$key = $value;
    	}
    }

    public function load($data)
    {
    	foreach($data as $key=>$value){
    		if (!isset($this->$key)){
    			$this->$key = $value;
    		}
    	}
    }

    public function isIn(array $data)
    {
        $names = array_map(function($i){
            return $i->aliases;
        }, $data);
        $websites = array_map(function($i){
            return $i->website;
        }, $data);
        $twitters = array_map(function($i){
        	return $i->twitter_id;
        }, $data);
        $facebooks = array_map(function($i){
        	return $i->facebook_id;
        }, $data);

        $c = count($names);
        for ($i = 0; $i < $c; $i++){
        	if (array_search($this->name, $names[$i]) !== false){
        		return $i;
        	}
        }

        if (isset($this->website)){
	        if (($i = array_search($this->website, $websites)) !== false){
	        	return $i;
	        }
	    }
        if (isset($this->twitter_id)){
	        if (($i = array_search($this->twitter_id, $twitters)) !== false){
	        	return $i;
	        }
        }
        if (isset($this->facebook_id)){
	        if (($i = array_search($this->facebook_id, $facebooks)) !== false){
	        	return $i;
	        }
        }

        return false;
    }

    public static function fromArray($data){
    	$reps = [];
    	foreach($data as $repData){
        	array_push($reps, new Representative($repData));
    	}
    	return $reps;
    }
}
