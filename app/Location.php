<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class Location extends Eloquent
{

    protected $collection = 'locations';
    protected $pimaryKey = '_id';

    public $timestamps = false;

    public static function byZip($zip)
    {
    	return self::where('zip',$zip)->get();
    }
}
