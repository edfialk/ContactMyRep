<?php

use Illuminate\Database\Seeder;

use App\Location;
use App\Representative;

use App\Providers\Sunlight\StateAPI;
use App\Providers\Sunlight\CongressAPI;
use App\Providers\Google\GoogleAPI;

class RepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// UNCOMMENT BELOW LINE TO RE-DOWNLOAD DATA
    	// $this->downloadStates();

    	// $this->congress();
    	$this->states();
    	// $this->google();
    }

    public function google()
    {
    	$google = new GoogleAPI();
    	$states = Location::states;
    	$donePresident = false;

    	foreach($states as $state_abbrev => $state_name){
    		echo "state: ".$state_name."\n";
    		$req = $google->address($state_name)->then(function($data) use ($donePresident){
    			foreach($data->reps as $rep){
                    if ($rep->office == 'President' && !$donePresident){
                        unset($rep->state);
                        $rep->save();
                        $donePresident = true;
                    }else if (Representative::exists($rep)){
    					$old = Representative::find($rep);
    					foreach($rep->getAttributes() as $k=>$v){
    						if (!empty($v)) $old->$k = $v;
    					}
    					$old->save();
    				}else{
	    				if ($rep->office == 'Governor')
	    					$rep->save();
    				}
    			}
    		});
    		$req->wait();
    	}
    }

    public function states()
    {
        $api = new StateAPI();
        foreach(Location::states as $state=>$state_name){
            $request = $api->state($state)->then(function($data) use ($api){
                foreach($data as $d){
                    if (!isset($d['state']) || !isset($d['chamber']) || !isset($d['district'])) continue;

                    $d = $api->format($d);
                    $division = 'ocd-division/country:us/state:'.$d['state'].'/sld';
                    $division .= $d['chamber'] == 'upper' ? 'u' : 'l' ;
                    $division .= ':'.$d['district'];
                    echo "division: $division \n";

                    $rep = Representative::firstOrCreate(['division' => $division]);
                    $rep->load($d);
                    $rep->addSource('openstates');
                    $rep->save();
                }
            });
            $request->wait();
        }
    }

    public function congress()
    {
 		$path = 'resources/assets/data/reps/congress.csv';
		$data = $this->readCSV($path);
	    $data = CongressAPI::validate($data);
	    foreach($data as $d){
            $d['source'] = ['opencongress'];
	    	$rep = Representative::fromData($d);
	    	if (! Representative::exists($rep) ){
	    		$rep->save();
            }
	    }
    }

    public function readCSV($path, $removeHeader = true)
    {
    	echo "\nreading file: $path ... \n";
    	$csv = array_map('str_getcsv', file($path));
	    array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });

	    if ($removeHeader) array_shift($csv);

	    return $csv;
	}

}

