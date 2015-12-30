<div class="row location">
	<div class="col s12 center-align">
		<p class="flow-text">
			{{ $location }}
		</p>
		@if (isset($gps))
			<a href='https://www.google.com/search?q={{ urlencode($gps) }}'>{{ $gps }}</a>
		@endif
	</div>
</div>
