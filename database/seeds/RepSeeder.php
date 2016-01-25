<?php

use Illuminate\Database\Seeder;

use App\Representative;

use App\Providers\Sunlight\StateAPI;
use App\Providers\Sunlight\CongressAPI;

class RepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// $this->seedCongress();
    	// UNCOMMENT BELOW LINE TO RE-DOWNLOAD DATA
    	// $this->downloadStates();
    	$this->seedStates();
    }

    public function seedStates()
    {
		$keys = [
			'full_name' => 'name',
			'first_name',
			'middle_name',
			'last_name',
			'suffixes',
			'nickname',
			'chamber',
			'district',
			'state',
			'photo_url' => 'photo'
		];

		$dir = 'resources/assets/data/';
		$di = new RecursiveDirectoryIterator($dir);
		foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
			if (stripos($filename, 'legislators.csv') === false)
				continue;

			$data = $this->csvToArray($path, $keys);
		    $reps = StateAPI::validate($csv);
		    $this->save($reps);
		}
    }

    public function seedCongress()
    {
		$congressAPIKeys = [
			'bioguide_id',
			'district',
			'facebook_id',
			'firstname',
			'fax',
			'lastname',
			'middlename',
			'name_suffix',
			'nickname',
			'ocd_id' => 'division_id',
			'congress_office' => 'address',
			'party',
			'phone',
			'state',
			'state_name',
			'title',
			'twitter_id',
			'website',
			'webform' => 'contact_form',
			'votesmart_id'

		];

		$path = 'resources/assets/data/congress.csv';
		$data = $this->csvToArray($path, $keys);
	    $reps = CongressAPI::validate($data);

	    $this->save($reps);
    }

    public function csvToArray($path, $keys, $removeHeader = true)
    {
    	echo "\nreading file: $path ... \n";
    	$csv = array_map('str_getcsv', file($path));
	    array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });

	    if ($removeHeader) array_shift($csv);

	    return $csv;
	}

	public function save($reps)
	{
	    foreach($reps as $rep){
        	if ( Representative::where('name', $rep->name)->where('state', $rep->state)->where('district', $rep->district)->count() == 0 ){
        		$rep->save();
        	}
	    }

        echo "done\n";
    }


}

