<?php

use Illuminate\Database\Seeder;
use App\Location;

class LocationSeeder extends Seeder
{

    const baseurl = "http://www2.census.gov/geo/relfiles/";
    const ext = ".txt";

    private $filenames = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->stateDistricts();
    	$this->congressionalDistricts();
    }

    public function congressionalDistricts()
    {
    	$file = file('resources/assets/data/locations/zipToCD.csv');
    	$count = 0;
    	for ($i = 1; $i < count($file); $i++){
    		$pieces = explode(",", $file[$i]);
    		$loc = Location::where('zip', intval($pieces[0]))->first();
    		if (is_null($loc)){
    			$loc = new Location();
    			$loc->zip = intval($pieces[0]);
    		}
    		$loc->state = $pieces[1];
    		$cd = trim($pieces[2]);
    		if (isset($loc->cd)){
    			if (!in_array($cd, $loc->cd)){
    				$cds = $loc->cd;
    				array_push($cds, $cd);
    				$loc->cd = $cds;
    				$loc->save();
    				$count++;
    			}
    		}else{
    			$loc->cd = [$cd];
    			$loc->save();
    			$count++;
    		}
    	}
    	echo "num saved: $count \n";
    }

    /**
     * I really should've saved these to local
     */
    public function stateDistricts()
    {
    	$this->download(self::baseurl."cdsld14/", 0); //newest first will not get overwritten
    	$this->download(self::baseurl."cdsld13/", 0);
    }

    // from https://www.census.gov/geo/maps-data/data/sld_state.html
    // example: http://www2.census.gov/geo/relfiles/cdsld14/02/zc_lu_delim_02.txt
    public function download($url, $start = 0)
    {
    	libxml_use_internal_errors(true);
    	$ht = file_get_contents($url);
        $doc = new DOMDocument();
        $doc->loadHTML($ht);
        $x = new DOMXpath($doc);
        $table = $x->query('//div[@id="innerPage"]//table')[0];
        $rows = $x->query('./tr', $table);
        for($i = 3; $i<$rows->length; $i++){
        	$row = $rows[$i];
        	$a = $x->query('./td[2]/a', $row)[0];
        	if (is_null($a)){
        		return;
        	}
        	$folder = $a->getAttribute('href');
        	$state_id = intval(str_replace("/", "", $folder));
        	if ($state_id < $start)
        		continue;
        	$page = file_get_contents($url.$folder);
        	$subdoc = new DOMDocument();
        	$subdoc->loadHTML($page);
        	$xp = new DOMXpath($subdoc);
        	$t = $xp->query('//div[@id="innerPage"]//table//a');
        	foreach($t as $a){
        		$link = $a->getAttribute('href');
        		if (in_array($link, $this->filenames))
        			continue;
        		else
        			array_push($this->filenames, $link);

        		if (stripos($link, 'zc_l') !== false && stripos($link, '_delim_') !== false){
        			$isUpperChamber = ( stripos($link, "lu") !== false );

        			echo "\nnew file: $link - ";
        			$file = file($url.$folder.$link);
        			$state = substr($file[0], 0, stripos($file[0], ' STATE'));
        			echo "$state - ";
        			echo $isUpperChamber ? "upper\n" : "lower\n";

        			$this->writeFile($file, $isUpperChamber ? 'u' : 'l', $state);
        			$this->parseFile($file, $isUpperChamber ? 'u' : 'l', $state);
        		}
        	}
        }
    }

    public function writeFile($file, $chamber, $state)
    {
    	File::put('resources/assets/data/locations/zipToStateLeg-'.$chamber.'-'.$state.'.csv');
    }

    public function parseFile($file, $chamber, $state)
    {
		//first word of first line is state
		//line 2 is tags
		//state,zip,sld
    	$count = 0;
		for ($j = 2; $j< count($file); $j++){
			$line = explode(",", $file[$j]);
			$zip = trim($line[1]);
			$district = ltrim(trim($line[2]), "0");

			$loc = Location::where('zip', intval($line[1]))->first();
			if (is_null($loc)){
				$loc = new Location();
				$loc->zip = intval($zip);
			}
			$loc->state_name = ucwords(strtolower($state));
			$sld = 'sld'.$chamber;
			if (isset($loc->$sld)){
				$districts = $loc->$sld;
				if (!in_array($district, $districts)){
					array_push($districts, $district);
	    			$loc->$sld = $districts;
	    			$count++;
				}
			}else{
				$loc->$sld = [$district];
				$count++;
			}

			$loc->save();
		}
		echo "count: $count\n";
    }

}

