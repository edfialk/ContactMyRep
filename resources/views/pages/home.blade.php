@extends('layout')

@section('content')

<div class="container">

	<div class="row logo">
	</div>

	<div class="row search">
		<form class="col s12 m10 offset-m1">
			<div class="row">
				<div class="input-field col s12 m10">
					<input type='text' id='input' placeholder='Zip code, address, or state + district'>
				</div>
				<div class="input-field col s12 m2">
					<input type="submit" class="waves-effect waves-light btn" value="Search">
				</div>
			</div>
		</form>
	</div>

	@if (isset($location))
		@include('pages.location')
	@endif

	<hr>

	@if (count($reps) > 0)
		@include('pages.results')
	@endif

</div>

@stop

@section('scripts.body')

	<script src="{{ URL::asset('js/main.js') }}"></script>
    <script async defer
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBD-WBhPqQZUEuGI8HBFOiVXtT6q9Xt5qc&amp;callback=initMap">
    </script>
@stop