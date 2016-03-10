@extends('layout')

@section('content')

<div id="app" class="container-fluid">

	@include('partials/_nav')

	<component :is="currentView" :query="query" :geolocation="geolocation"></component>

	@if (Auth::check())
		<input type="hidden" id="role" value="{{ Auth::user()->id }}">
	@endif

</div>
@stop

@section('scripts.body')
	@if (isset($location))
		<script>var ipinfo = {!! json_encode($location) !!}</script>
	@endif

	<script src="{{ URL::asset('js/main.js') }}"></script>
@stop
