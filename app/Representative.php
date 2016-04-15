<?php

namespace App;

use MongoDB\BSON\Regex as MongoRegex;
use Jenssegers\Mongodb\Eloquent\Model as Model;


class Representative extends Model
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
		['nickname','last_name','name_suffix'],
		['first_name','nickname','last_name'],
		['first_name','nickname','last_name','name_suffix'],
		['nickname','middle_name','last_name'],
		['nickname','middle_name','last_name','name_suffix'],
		['first_name','last_name'],
		['first_name','last_name','name_suffix'],
		['first_name','middle_name','last_name'],
		['first_name','middle_name','last_name','name_suffix']
	];

	//defaults
	protected $attributes = [
		'sources' => [],
		'phones' => [],
		'addresses' => [],
		'aliases' => [],
		'urls' => [],
		'emails' => [],
	];

    protected $guarded = [];

	protected $collection = 'reps'; //mongo table name
	protected $primaryKey = '_id';

    public $timestamps = false;

    public function save(array $options = array())
    {
        if (stripos($this->first_name, " ") !== false && empty($this->middle_name)){
            $p = explode(" ", $this->first_name);
            if (count($p) == 2){
                $this->first_name = $p[0];
                $this->middle_name = $p[1];
            }else{
                $this->first_name = array_shift($p);
                $this->middle_name = implode(" ", $p);
            }
        }

        if (strlen($this->middle_name) == 1){
            $this->middle_name = $this->middle_name.'.';
        }

    	$this->setAliases();
    	$this->setDivision();
    	$this->setPhoto();

    	parent::save();
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    /**
     * copy info from data - overwrites present info!
     * @param  array $data input
     * @return null
     */
    public function load($data)
    {
        foreach($data as $key=>$value){
            if (empty($value)) continue;
            if (is_string($this->$key) && is_string($value) && strcasecmp($this->$key, $value) !== 0){
                //don't replace if case insensitive equals
                $this->$key = $value;
            }else if ($this->$key != $value){
                $this->$key = $value;
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

		if (count($results) > 0){
			$this->aliases = $results;
            if (empty($this->name)) $this->name = $results[0];
		}
    }

    public function setDivision()
    {
    	if (isset($this->division)) return;
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
     * get relative url for local rep photos
     * @param  Representative $rep
     * @return string      relative url
     */
    public function setPhoto()
    {
        if (isset($this->photo)) return;

        $dir = '/images/reps/';
        $ext = '.jpg';

        if (isset($this->name)){
        	$pieces = explode(" ", $this->name);
        	if (count($pieces) < 2) return;

        	$first = array_shift($pieces);
        	array_push($pieces, $first);

        	$filename = $dir.implode("-", $pieces).$ext;

            if (\File::exists(public_path().$filename)){
                $this->photo = $filename;
            }
        }
    }

    public function setAddressAttribute($val)
    {
        $val = is_array($val) ? array_map('ucwords', $val) : [ucwords($val)];
        $this->attributes['address'] = $val;
    }
	/**
	 * ensure state abbreviation is always 2 digit caps
	 * @param string $val state abbreviation
	 */
	public function setStateAttribute($val)
	{
		if (strlen($val) == 2)
			$this->attributes['state'] = strtoupper($val);
		else
			$this->attributes['state_name'] = $val;
	}

	/**
	 * ensure office is one of accepted values
	 * @param string $name office name
	 */
	public function setOfficeAttribute($name)
	{
		if (stripos($name, 'house of representatives') !== false){
			$this->attributes['office'] = 'House of Representatives'; //remove district
		}else if (stripos($name, 'state house') !== false || stripos($name, 'state assembly') !== false){
			$this->attributes['office'] = 'State House';
		}else if (stripos($name, 'state senate') !== false){
			$this->attributes['office'] = 'State Senate';
		}else{
			$this->attributes['office'] = str_replace(["United States ", " of the United States"], "", $name);
		}
	}

    public function hasSource($src)
    {
        return in_array($src, $this->sources);
    }

	public function addSource($src)
	{
		if (!in_array($src, $this->sources)){
			$srcs = $this->sources;
			array_push($srcs, $src);
			$this->sources = $srcs;
		}
	}

	/**
	 * check if supplied office is on our approved list
	 * @param  string  $office
	 * @return boolean
	 */
    public static function isValidOffice($office)
    {
    	$temp = new Representative();
    	$temp->office = $office;
    	return array_search($temp->office, array_keys(self::offices)) !== false;
    }

    public function scopeName($query, $name)
    {
        $reg = new MongoRegex($name, 'gi');
        return $query->whereIn('aliases', array($reg))->orWhere('name', 'like', '%'.$name.'%');
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

    public function scopeAtLocation($query, $location)
    {
    	$resp = [];
        //US house
        if (isset($location->cd)){
	        foreach($location->cd as $cd){
                $q = self::cd($location->state, $cd)->first();
                if (null !== $q) $resp[] = $q;
	        }
	    }

        //state district upper
        if (isset($location->sldu)){
	        foreach($location->sldu as $sldu){
                $q = self::sldu($location->state, $sldu)->first();
                if (null !== $q) $resp[] = $q;
	        }
	    }

        //state district lower
        if (isset($location->sldl)){
	        foreach($location->sldl as $sldl){
                $q = self::sldl($location->state, $sldl)->first();
                if (null !== $q) $resp[] = $q;
	        }
	    }

        //governor and US senators
        $q = self::state($location->state)->get()->all();
        if (is_array($q)) $resp = array_merge($resp, $q);

        //president
        array_push($resp, self::where("office", "President")->first());

        return $resp;
    }

    public static function exists($rep)
    {
    	return null !== self::find($rep);
    }

    public static function find($query)
    {
        if (isset($query->first_name) && isset($query->last_name)){
            $first = $query->first_name;
            if (stripos($first, " ") !== false){
                $first = explode(" ", $first)[0];
            }
            $query->name = $first.' '.$query->last_name;
        }
    	$reps = Representative::name($query->name)->get()->all();
    	if (count($reps) == 1){
    		return $reps[0];
    	}else if (count($reps) > 1){
    		$reps = array_filter($reps, function($r) use ($query){
    			if (isset($query->division))
    				return $query->division == $r->division;
    			if (isset($query->district) && isset($query->state))
    				return $query->district == $r->district && $query->state == $r->state;
    		});
	    	if (count($reps) === 1){
	    		return array_pop($reps);
	    	}
    	}

    	return null;
    }
}
