<?php

namespace App\Providers\Sunlight;

use Illuminate\Support\ServiceProvider;
use App\Providers\Sunlight\SunlightAPI;

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
        $this->app->singleton(SunlightAPI::class, function($app){
            return new SunlightAPI();
        });
    }

    public function provides()
    {
        return [SunlightAPI::class];
    }
}
