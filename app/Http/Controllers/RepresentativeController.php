<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use SunlightAPI;
use App\Providers\Opensecrets\Opensecrets;

use App\Representative;

class RepresentativeController extends Controller
{

    protected $sunlight;
    protected $opensecrets;

    public function __construct(OpenSecrets $os)
    {
        $this->opensecrets = $os;
    }

    public function show($zipcode)
    {
        $data = SunlightAPI::getRepsByZipCode($zipcode);

        $reps = [];

        foreach ($data->results as $result){
            array_push($reps, new Representative($result));
        }

        return view('pages.zip', ['reps' => $reps]);
    }

}
