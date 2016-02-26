<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;


use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;
use StateAPI;
use Log;

class SyncController extends Controller
{

    public function openstates($max = 100)
    {
        $max = intval($max);
        $requests = [];
        $client = StateAPI::client();

        $reps = Representative::where('division', 'like', '%/sld%')->whereNotNull('sources')->whereNotIn('sources', ['openstates'])->take($max)->get();

        foreach($reps as $rep){
            $url = 'legislators/?district='.$rep->district.'&state='.$rep->state.'&chamber='.$rep->chamber;
            echo $url."<br>";

            $requests[] = $client->getAsync($url)->then(

                function(ResponseInterface $res) use ($fields){
                    $data = json_decode($res->getBody(), true);
                    if (count($data) == 1){
                        $data = StateAPI::format($data[0]);
                        $division = 'ocd-division/country:us/state:'.$data['state'].'/';
                        $division .= $data['chamber'] == 'lower' ? 'sldl:' : 'sldu:';
                        $division .= $data['district'];
                        echo "division: ".$division."<br>";
                        $r = Representative::where('division', $division)->firstOrCreate();
                        $r->load($data);
                        dd($r);
                        $r->addSource('openstates');
                        $r->save();

                        echo "$name --> ".$r->name."<br>";
                        echo "-- $dist --> ".$r->district."<br>";
                        echo "-- $state --> ".$r->state."<br>";
                    }else{
                        echo "<br>UNEXPECTED DATA COUNT: ".count($data)."<br>";
                        echo "<pre>";
                        print_r($data);
                        echo "</pre>";
                        die();
                    }

                },
                function(RequestException $e){
                    return [];
                }
            );
        }

        $results = Promise\unwrap($requests);
    }
}
