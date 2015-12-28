<?php

namespace App\Repositories;

interface RepRepository {
	public function gps($lat, $lng);
	public function district($state, $district);
	public function zip($zip);
}