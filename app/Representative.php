<?php

namespace App;

use App\Repositories\RepRepository;
use Jenssegers\Mongodb\Model as Eloquent;

class Representative extends Eloquent
{

	const offices = [ // in display order
	    'Senate' 					=> 'ocd-division/country:us/state:$state',
	    'House of Representatives'  => 'ocd-division/country:us/state:$state/cd:$district',
	    'State Senate'				=> 'ocd-division/country:us/state:$state/sldu:$district',
	    'State House'				=> 'ocd-division/country:us/state:$state/sldl:$district',
	    'Mayor'						=> 'ocd-division/country:us/state:$state/place:$city',
	    'Governor'					=> 'ocd-division/country:us/state:$state/',
	    'President'					=> 'ocd-division/country:us'
	];
	const aliases = [
		['nickname','last_name'],
		['nickname','middle_name','last_name'],
		['nickname','middle_name','last_name','name_suffix'],
		['first_name','last_name'],
		['first_name','middle_name','last_name'],
		['first_name','middle_name','last_name','name_suffix']
	];

	protected $collection = 'reps';
	protected $primaryKey = '_id';
    protected $hidden = ['_id'];
    protected $fillable = ['office'];

	public $timestamps = false;

    public static function fromData($data, $keys = null)
    {
    	$rep = new Representative;
    	$rep->load($data, $keys);

    	//computed properties
    	$rep->setAliases();
    	$rep->setDivision();
    	$rep->setPhoto();

    	return $rep;
    }

    /**
     * copy info from data -  overwrites present info!
     * @param  array $data input
     * @param  array $keys string names of fields to copy from data.
     *                     If entry is a key=>value, data->key will be copied to this->value
     * @return null
     */
    public function load($data, $keys = null)
    {
    	if (is_array($data)) $data = (object) $data;
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

    /**
     * populate name aliases for this rep
     */
    public function setAliases()
    {
		$results = [];
		foreach(self::aliases as $a){
			$parts = [];
			foreach($a as $key){
				if (empty($this->$key))
					continue 2;
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

    public function setDivision()
    {
    	if (isset($this->office)){
    		$division = self::offices[$this->office];
    		$pattern = '/\$([a-z]+)/i';
    		$this->division = preg_replace_callback($pattern, function($matches){
    			$k = $matches[1];
    			$v = $this->$k;
    			return str_replace(" ", "_", strtolower($v));
    		}, $division);
    	}
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
    	return array_search($temp->office, array_keys(self::offices)) !== false;
    }

    /**
     * get relative url for local rep photos
     * @param  Representative $rep
     * @return string      relative url
     */
    public function setPhoto()
    {
        $dir = '/images/reps/';
        $ext = '.jpg';

        if (isset($this->name)){
        	$pieces = explode(" ", $this->name);
        	if (count($pieces) < 2) return;

        	$first = array_shift($pieces);
        	array_push($pieces, $first);

        	$filename = $dir.implode("-", $pieces).$ext;

            if (\File::exists(public_path().$filename)){
                $this->photo = "http://contactmyreps.org".$filename;
            }
        }
    }

    public function scopeState($query, $state)
    {
    	return $query->where('division', 'ocd-division/country:us/state:'.strtolower($state));
    }

    public function scopesldl($query, $state, $district)
    {
    	return $query->where('division','ocd-division/country:us/state:'.strtolower($state).'/sldl:'.$district);
    }

    public function scopesldu($query, $state, $district)
    {
    	return $query->where('division', 'ocd-division/country:us/state:'.strtolower($state).'/sldu:'.$district);
    }

    public function scopecd($query, $state, $district)
    {
    	return $query->where('division', 'ocd-division/country:us/state:'.strtolower($state).'/cd:'.$district);
    }

    public function scopeLocation($query, $location)
    {
    	$resp = [];
        //US house
        foreach($location->cd as $cd){
            foreach( self::cd($location->state, $cd)->get() as $rep){
                $resp[] = $rep;
            }
        }

        //state district upper
        foreach($location->sldu as $sldu){
            foreach( self::sldu($location->state, $sldu)->get() as $rep){
                $resp[] = $rep;
            }
        }

        //state district lower
        foreach($location->sldl as $sldl){
            foreach( self::sldl($location->state,$sldl)->get() as $rep){
                $resp[] = $rep;
            }
        }

        //governor and US senators
        foreach( self::state($location->state)->get() as $rep){
            $resp[] = $rep;
        }

        //president
        $rep = self::where('office','President')->first();
        $resp[] = $rep;

        self::sortByRank($resp);

        return $resp;
    }

    public static function sortByRank(&$reps)
    {
    	usort($reps, function($a, $b){
            $ranks = array_keys(Representative::offices);
            $ia = array_search($a->office, $ranks);
            $ib = array_search($b->office, $ranks);

            if ($ia === false) $ia = 6;
            if ($ib === false) $ib = 6;

            return $ia > $ib;
    	});
    }

    public static function exists($rep)
    {
    	return !is_null(self::find($rep));
    }

    public static function find($rep)
    {
    	if (isset($rep->division)){
			return Representative::where('division', $rep->division)->where('name', $rep->name)->first();
    	}else if (isset($rep->district)){
    		return Representative::where('district', $rep->district)->where('state', strtoupper($rep->state))->where('name', $rep->name)->first();
    	}
    }

    public static function sync($data)
    {
    	if (!is_array($data)) $data = [$data];

	    $sync = array_filter($data, function($rep){
	        return isset($rep['office']) && in_array($rep['office'], ['State House', 'State Senate', 'Senate', 'Representative']);
	    });

	    foreach($sync as $grep){
	    	if (is_array($grep)) $grep = (object) $grep;
	        $rep = Representative::find($grep);
	        if (!is_null($rep)){
	            foreach($grep as $k=>$v){
	                if (!empty($v) && !isset($rep->$k)){
	                    $rep->$k = $v;
	                }
	            }
	            $rep->save();
	        }
	    }

    }

}
