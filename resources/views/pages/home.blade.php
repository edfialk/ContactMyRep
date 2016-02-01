@extends('layout')

@section('content')
<div class="container">

	<div class="row logo"></div>
	<div class="row search">
		<form v-on:submit="search" class="col-sm-12">
			<div class="row form-group">
				<input type="text" id="input" v-model="query | search" placeholder="Zip code or address" class="col-sm-12 text-center form-control">
			</div>
			<div class="row form-group">
				<div class="status-wrapper col-sm-12 text-center" v-show="status.length > 0" transition="fade" style="display: none;">
					<span class="status red accent-1 z-depth-1" v-text="status"></span>
				</div>
			</div>
			<div class="row form-group">
				<div class="input-field col-sm-12 text-center">
					<input type="submit" class="btn btn-success btn-lg" value="Search">
					<input type="button" class="enable-geo btn btn-success btn-lg" value="Use my location" v-on:click="locate">
				</div>
			</div>
		</form>
	</div>

	<div class="row location">
		<div class="col-sm-12 text-center">
			<h4>
				<span class="city" v-if="city" v-text="city"></span>
				<span class="state" v-if="state" v-text="state"></span>
				<span class="zip" v-if="zip" v-text="zip"></span>
			</h4>
		</div>
	</div>

	<div class="row results">
	  	<div class="col-sm-6 col-sm-offset-3" v-show="loading">
			<div class="progress progress-striped active">
			  <div class="progress-bar" style="width: 100%"></div>
			</div>
  		</div>
		<div class="col-sm-12" v-show="!loading">
			<table class="table">
				<tbody>
					<tr is="item" v-for="item in reps" :item="item" :role="role"></tr>
				</tbody>
			</table>
		</div>
	</div>

	@if (isset($location))
		@if (isset($location->loc))
			<input type='hidden' id='gps' value='{{ $location->loc }}'>
		@endif
		@if (isset($location->city))
			<input type='hidden' id='city' value='{{ $location->city }}' v-model="city">
		@endif
		@if (isset($location->region))
			<input type='hidden' id='state' value='{{ $location->region }}' v-model="state">
		@endif
		@if (isset($location->postal))
			<input type='hidden' id='zip' value='{{ $location->postal }}' v-model="zip">
		@endif
	@endif

	@if (Auth::check())
		<input type="hidden" id="role" value="{{ Auth::user()->id }}">
	@endif
</div>
@stop

@section('scripts.body')
	<script src="{{ URL::asset('js/main.js') }}"></script>
@stop