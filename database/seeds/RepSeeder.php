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
    	// $this->states();
    	$this->google();
    }

    public function google()
    {
    	$google = new GoogleAPI();
    	$states = Location::states;
    	$donePresident = false;

    	foreach($states as $state_abbrev => $state_name){
    		echo "state: ".$state_name."\n";
    		$req = $google->address($state_name)->then(function($data) use ($donePresident){
    			if (empty($data->reps)){
    				return;
    			}
    			foreach($data->reps as $rep){
    				if (Representative::exists($rep)){
    					$old = Representative::find($rep);
    					foreach($rep->getAttributes() as $k=>$v){
    						if (!empty($v)) $old->$k = $v;
    					}
    					$old->save();
    					Log::info('merged rep: '.$rep->name);
    				}else{
	    				if ($rep->office == 'President' && !$donePresident){
	    					unset($rep->state);
	    					$rep->save();
	    					$donePresident = true;
	    				}
	    				if ($rep->office == 'Governor'){
	    					$rep->save();
	    					Log::info('found governor: '.$rep->name);
	    				}
    				}
    			}
    		});
    		$req->wait();
    	}
    }

    public function states()
    {
		$dir = 'resources/assets/data/';
		$di = new RecursiveDirectoryIterator($dir);
		foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
			if (stripos($filename, 'legislators.csv') === false)
				continue;

			$data = $this->csvToArray($filename);
		    $data = StateAPI::validate($data);
		    foreach($data as $d){
		    	$rep = Representative::fromData($d);
		    	if (! Representative::exists($rep) )
		    		$rep->save();
		    }
		}
    }

    public function congress()
    {
 		$path = 'resources/assets/data/congress.csv';
		$data = $this->csvToArray($path);
	    $data = CongressAPI::validate($data);
	    foreach($data as $d){
	    	$rep = Representative::fromData($d, CongressAPI::keys);
	    	if (! Representative::exists($rep) )
	    		$rep->save();
	    }
    }

    public function csvToArray($path, $removeHeader = true)
    {
    	echo "\nreading file: $path ... \n";
    	$csv = array_map('str_getcsv', file($path));
	    array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });

	    if ($removeHeader) array_shift($csv);

	    return $csv;
	}

	public function save(array $reps)
	{
	    foreach($reps as $rep){
        	if ( Representative::where('name', $rep->name)
        		->where('state', $rep->state)
        		->where('district', $rep->district)
        		->count() == 0
        	){
        		$rep->save();
        	}
	    }

        echo "done\n";
    }


}

