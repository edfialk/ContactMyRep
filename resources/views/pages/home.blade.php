@extends('layout')

@section('content')

<div id="home" class="container-fluid">

	@include('partials/_nav')

	<div class="row loading" v-show="loading">
		@include('partials/_loader')
	</div>

	<div class="row status" v-show="status">
		<div class="col-xs-12 text-center">
			<h4>
				<span v-text="status"></span>
			</h4>
		</div>
	</div>

	<div class="row location" v-show="hasResults">
		<div class="col-xs-12 text-center">
			<h4>
				<span v-text="printSearch"></span>
			</h4>
		</div>
	</div>

	<div class="row results">
		<div class="col-xs-12" v-show="!loading">
			<item v-for="item in reps" :item="item" :role="role"></item>
		</div>
	</div>

	@if (Auth::check())
		<input type="hidden" id="role" value="{{ Auth::user()->id }}">
	@endif

</div>
@stop

@section('scripts.body')
	@if (isset($location))
		<script>var ipinfo = {!! json_encode($location) !!}</script>
	@endif

	<script src="{{ URL::asset('js/home.js') }}"></script>
@stop
