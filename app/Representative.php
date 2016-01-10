<?php

namespace App;

use App\Repositories\RepRepository;

class Representative
{

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

    public static function fromArray($data){
    	$reps = [];
    	foreach($data as $repData){
        	array_push($reps, new Representative($repData));
    	}
    	return $reps;
    }
}
