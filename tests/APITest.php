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

    public function testAddress()
    {
        $faker = \Faker\Factory::create();
        $add = $faker->streetAddress();
        $zip = $faker->postcode();
        $this->get('/api/v1/'.$add.','.$zip)->seeJson();
    }

    public function testGps()
    {
        $faker = \Faker\Factory::create('en_US');
        $lat = $faker->randomFloat(4,24,51);
        $lng = $faker->randomFloat(4,-127.62,-66.3);
        $url = '/api/v1/'.$lat.'/'.$lng;
        $this->get($url)->seeJson();
    }

    public function testState()
    {
        $faker = \Faker\Factory::create();
        $this->get('/api/v1/'.$faker->state())->seeJson();
        $this->get('/api/v1/'.$faker->stateAbbr())->seeJson();
    }

}
