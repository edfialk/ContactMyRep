<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Promise;


use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;
use StateAPI;
use Log;

class SyncController extends Controller
{

    public function openstates()
    {
        $requests = [];
        $reps = Representative::where('division', 'like', '%/sld%')->whereNotIn('sources', ['openstates'])->whereNotNull('sources')->take(100)->get();
        echo 'Total reps: '.count($reps).'<br>';
        foreach($reps as $rep){
            echo count($requests).": ".$rep->name."<br>";
            $requests[] = StateAPI::async('legislators?district='.$rep->district.'&state='.$rep->state.'&last_name='.$rep->last_name);
            if (count($requests) > 100) break;
        }

        $results = Promise\unwrap($requests);
    }
}
