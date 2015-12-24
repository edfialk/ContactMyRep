<?php

namespace App\Providers\Sunlight;

use Illuminate\Support\Facades\Facade;

class StateAPIFacade extends Facade {
    protected static function getFacadeAccessor() { return 'App\Providers\Sunlight\StateAPI'; }
}