<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Model;

class Report extends Model
{

	protected $fillable = ['text'];

    public function representative()
    {
    	return $this->belongsTo('App\Representative');
    }
}
