<?php

namespace App;

class Representative
{

	protected $fields = [
		'bioguide_id',
		'birthday',
		'chamber',
		'contact_form',
		'crp_id',
		'district',
		'facebook_id',
		'fec_id',
		'fax',
		'first_name',
		'govtrack_id',
		'last_name',
		'name_suffix',
		'nickname',
		'ocd_id',
		'office',
		'party',
		'phone',
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

    public function __construct($data)
    {
    	foreach ($data as $key=>$value){
    		if (in_array($key, $this->fields) && !is_null($value)){
    			$this->$key = $value;
    		}
    	}

    	if (!isset($this->name)){
    		$this->setName();
    	}
    }

    public function setName()
    {

    	$parts = [];
    	if (isset($this->nickname))
    		array_push($parts, $this->nickname);
    	else
    		array_push($parts, $this->first_name);
    	if (isset($this->last_name))
    		array_push($parts, $this->last_name);
    	if (isset($this->name_suffix))
    		array_push($parts, $this->name_suffix);
    	if (isset($this->party))
    		array_push($parts, '['.$this->party.']');

    	$this->name = implode(" ", $parts);
    }

    public function print_thomas_link()
    {
    	return "https://www.congress.gov/member/".$this->first_name."-".$this->last_name."/".$this->thomas_id;
    }
}
