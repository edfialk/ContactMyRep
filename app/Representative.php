<?php

namespace App;

use App\Repositories\RepRepository;

class Representative
{

/*	protected $rename = [
		'url' => 'website',
		'leg_id' => 'openstates_id',
		'office' => 'address',
		'+capitol_address' => 'address',
		'+district_address' => 'district_address',
		'+phone' => 'phone',
		'+district_phone' => 'district_phone'
	];*/

    public function __construct($data = [])
    {
    	// $renames = array_keys($this->rename);
    	foreach ($data as $key=>$value){
    		if (empty($value)) continue;

    		if ($key == 'state' && strlen($value) == 2) $value = strtoupper($value);

			$this->$key = $value;
    	}

/*    	$this->address = preg_replace_callback('#(Senate|House) Office Building#',
    		function($m) {
    			return $m[0][0].'OB, Washington, D.C.';
    		},
    		$this->address
    	);*/

/*    	if (isset($this->level) && $this->level == 'state' || isset($this->openstates_id)){
    		$this->title = 'State '.$this->title;
    	}*/

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
