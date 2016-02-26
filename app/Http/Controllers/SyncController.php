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

    public function openstates()
    {
        $max = 1;
        $requests = [];
        $fields = StateAPI::fields();
        $client = StateAPI::client();

        $reps = Representative::where('division', 'like', '%/sld%')->whereNotIn('sources', ['openstates'])->whereNotNull('sources')->take($max)->get();
        echo 'Total reps: '.count($reps).'<br>';

        foreach($reps as $rep){
            $url = 'legislators?district='.$rep->district.'&state='.$rep->state.'&chamber='.$rep->chamber;

            $requests[] = $client->getAsync($url)->then(

                function(ResponseInterface $res) use (&$rep, $fields){
                    $data = json_decode($res->getBody(), true);
                    if (count($data) == 1){
                        $name = $rep->name;
                        $data = $data[0];
                        foreach($fields as $key=>$value){
                            if (isset($data[$key])){
                                $rep->$value = $data[$key];
                            }
                        }
                        if (isset($data['offices']) && count($data['offices']) > 0){

                            $office = array_first($data['offices'], function($key, $value){
                                return $key == 'type' && $value == 'capitol';
                            }, $data['offices'][0]);

                            foreach($office as $k=>$v){
                                if (empty($v)) continue;

                                switch($k){
                                    case 'fax';
                                    case 'phone';
                                    case 'address';
                                    case 'email';
                                        $rep->$k = $v;
                                    break;
                                }
                            }

                        }
                        $rep->addSource('openstates');
                        $rep->save();
                        echo "$name --> ".$rep->name."<br>";
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
