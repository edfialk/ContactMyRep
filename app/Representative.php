<?php

namespace App;

use App\Repositories\RepRepository;

class Representative
{

	protected $fields = [
		'address',
		'bioguide_id',
		'birthday',
		'capitol_address',
		'chamber',
		'contact_form',
		'crp_id',
		'district',
		'district_address',
		'email',
		'facebook_id',
		'fec_id',
		'fax',
		'first_name',
		'govtrack_id',
		'last_name',
		'openstates_id',
		'name_suffix',
		'nickname',
		'ocd_id',
		'offices',
		'party',
		'phone',
		'photo_url',
		'state',
		'state_name',
		'term_end',
		'term_start',
		'thomas_id',
		'title',
		'twitter_id',
		'votesmart_id',
		'website',
		'youtube_id'
	];

	protected $rename = [
		'url' => 'website',
		'leg_id' => 'openstates_id',
		'office' => 'address',
		'+capitol_address' => 'capitol_address',
		'+district_address' => 'district_address'
	];

    public function __construct($data)
    {
    	foreach ($data as $key=>$value){
    		if (empty($value)) continue;
    		if ($key == 'state') $value = strtoupper($value);
    		// if (in_array($key, $this->fields)){
  				$this->$key = $value;
    		// }
			// if (in_array($key, array_keys($this->rename))){
			// 	$new_key = $this->rename[$key];
			// 	$this->$new_key = $value;
			// }
    	}

    	if (isset($this->chamber)){
    		if ($this->chamber == 'upper' || $this->chamber == 'senate'){
    			$this->title = 'Senator';
    		}else if ($this->chamber == 'lower' || $this->chamber == 'house'){
    			$this->title = 'Representative';
    		}
    	}

    }

    public function printName()
    {
    	$parts = [];
    	if (isset($this->nickname))
    		array_push($parts, $this->nickname);
    	else if (isset($this->first_name))
    		array_push($parts, $this->first_name);
    	if (isset($this->last_name))
    		array_push($parts, $this->last_name);
    	if (isset($this->name_suffix))
    		array_push($parts, $this->name_suffix);
    	if (isset($this->party))
    		array_push($parts, '['.substr($this->party, 0, 1).']');

    	return implode(" ", $parts);
    }

    public static function fromData($data){
    	$reps = [];
    	foreach($data as $repData){
        	array_push($reps, new Representative($repData));
    	}
    	return $reps;
    }

}
