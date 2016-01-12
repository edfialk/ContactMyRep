<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class APITest extends TestCase
{
    public function testZip()
    {
    	$faker = \Faker\Factory::create();
    	$zip = $faker->postcode();
        $this->get('/api/v1/'.$zip)->seeJson();
    }

    public function testGPS()
    {
        $faker = \Faker\Factory::create();
        $lat = $faker->latitude();
        $lng = $faker->longitude();
        $this->get('/api/v1/'.$lat.'/'.$lng)->seeJson();
    }
}
