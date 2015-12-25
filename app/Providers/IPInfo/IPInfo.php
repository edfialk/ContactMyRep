<?php

namespace App\Providers\IPInfo;

/**
* 	
*/
class IPInfo
{

	public static function getLocation($ip)
	{
		$data = file_get_contents("http://ipinfo.io/".$ip."/geo");
		return json_decode($data);
	}

}