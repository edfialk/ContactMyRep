@foreach ($location as $key=>$value)
	<input type='hidden' id='{{ $key }}' value='{{ $value }}'>
@endforeach
<div class="col s12 m6">
	<h5>
		@if (isset($location['city'])){{ $location['city'] }},@endif
		@if (isset($location['state'])){{ $location['state'] }}@endif
		@if (isset($location['zip'])){{ $location['zip'] }}@endif
	</h5>
	@if (count($districts) == 1)
		<h5>District {{ $districts[0] }}</h5>
	@elseif (count($districts) > 1)
		<p>Multiple districts in this zip code.</p>
	@endif
</div>
<input type='hidden' id='districts' value='{{ implode(",", $districts) }}'>
<div class="col s12 m6">
	<div class="map-container z-depth-1"><div id="map"></div></div>
</div>
