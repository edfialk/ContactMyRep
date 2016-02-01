<?php

//Common functions
function array_rename(array $data, array $keys)
{
	foreach($keys as $key=>$value){
		if (isset($data[$key])){
			$data[$value] = $data[$key];
			unset($data[$key]);
		}
	}
	return $data;
}

function rankSort($a, $b)
{
    $ranks = array_keys(App\Representative::offices);
    $ia = array_search($a->office, $ranks);
    $ib = array_search($b->office, $ranks);

    if ($ia === false) $ia = 6;
    if ($ib === false) $ib = 6;

    return $ia > $ib;
}

function divisions_split($array){
    $res = [
        'sldl' => [],
        'sldu' => [],
        'cd' => [],
        'state' => ''
    ];
    foreach($array as $d){
        foreach(explode("/", $d) as $piece){
        	$k = $piece[0];
        	$v = $piece[1];
        	if ($k == "country")
        		continue;
        	if ($k == "state")
        		$res->state = $v;
        	else if (isset($res->$k) && !in_array($v, $res->$k))
        		array_push($res[$k], $v);
        }
    }

    return $res;
}