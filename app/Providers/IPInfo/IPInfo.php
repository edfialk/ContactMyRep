<?php

namespace App\Providers\IPInfo;

/**
* 	
*/
class IPInfo
{

	public static function getLocation($ip)
	{
		if ($ip == "192.168.10.1") $ip = "73.157.212.42";

		$ip = urlencode($ip);
		$data = file_get_contents("http://ipinfo.io/".$ip."/geo");
		return json_decode($data);
	}

}