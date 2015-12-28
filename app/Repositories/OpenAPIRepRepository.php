<?php

namespace App\Repositories;

use App\Repositories\RepRepository;

use App\Representative;

use StateAPI;
use FederalAPI;

class OpenAPIRepRepository implements RepRepository {
	private $app;

	public function __construct(\App $app)
	{
		$this->app = $app;
	}

	public function zip($zip)
	{
		$data = FederalAPI::zip($zip);
		//OpenStates has no zip locator
		$loc = $this->findDistrict($data);
		foreach($loc['districts'] as $dist){
			$data = array_merge($data, StateAPI::district($loc['state'], $dist));
		}
		return Representative::fromData($data);
	}

	public function gps($lat, $lng)
	{
		$data = FederalAPI::gps($lat, $lng);
		//OpenStates gps locate is incredibly inaccurate, so i use district

		if (count($data) == 0) return [];

		$loc = $this->findDistrict($data);
		foreach($loc['districts'] as $dist){
			$data = array_merge($data, StateAPI::district($loc['state'], $dist));
		}
		return Representative::fromData($data);
	}

	public function district($state, $district)
	{
		$feds = FederalAPI::district($state, $district);
		$states = StateAPI::district($state, $district);
		return Representative::fromData(array_merge($feds, $states));
	}

	public function findDistrict($feds){
		$districts = [];
		$state = null;
        foreach($feds as $fed){
            if (isset($fed->district) && !in_array($fed->district, $districts)){
                array_push($districts, $fed->district);
            }
            if (!isset($state) && isset($fed->state)){
                $state = $fed->state;
            }
        }

        return ['state' => $state, 'districts' => $districts];
	}

}