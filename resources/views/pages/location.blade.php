<div class="row location">
	<div class="col s12 m6">
		<h5>{{ $location }}</h5>
		<p class='enable-geo'>Click here to make this more accurate.</p>
	</div>
	@if (isset($gps))
		<div class="col s12 m6">
			<input type='hidden' id='my-gps' value='{{ $gps }}'>
			<div class="map-container"><div id="map"></div></div>
		</div>
	@endif
</div>
