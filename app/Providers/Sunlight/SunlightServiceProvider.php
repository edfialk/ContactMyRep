<?php

namespace App\Providers\Sunlight;

use Illuminate\Support\ServiceProvider;
use App\Providers\Sunlight\FederalAPI;
use App\Providers\Sunlight\StateAPI;

class SunlightServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
        $this->setupConfig();
    }

    protected function setupConfig()
    {
/*        $source = realpath(__DIR__.'/../config/sunlightapi.php');
        $this->publishes([$source => config_path('sunlightapi.php')]);
        $this->mergeConfigFrom($source, 'sunlightapi');*/
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FederalAPI::class, function($app){
            return new FederalAPI();
        });
        $this->app->singleton(StateAPI::class, function($app){
            return new StateAPI();
        });
    }

    public function provides()
    {
        return [
            SunlightAPI::class,
            StateAPI::class
        ];
    }
}
