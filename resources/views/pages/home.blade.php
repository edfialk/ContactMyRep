@extends('layout')

@section('content')

<div class="container">

	<div class="row logo">
	</div>

	<div class="row search">
		<form class="col s10 offset-s1 l8 offset-l2">
			<div class="row">
				<div class="input-field col s9">
					<input type='text' id='input' placeholder='Zip code, address, or district'>
				</div>
				<div class="input-field col s1">
					<input type="submit" class="waves-effect waves-light btn" value="Search">
				</div>
			</div>
		</form>
	</div>

	@if (isset($location))
		@include('pages.location')
	@endif

	@if (count($reps) > 0)
		<div class="row results">
			@include('pages.results')
		</div>
	@endif

</div>

@stop

@section('scripts.body')

	<script src="{{ URL::asset('js/main.js') }}"></script>

@stop