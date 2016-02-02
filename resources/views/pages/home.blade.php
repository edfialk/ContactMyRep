@extends('layout')

@section('content')
<div class="home">
	<div class="container-fluid">
		<div class="row header">
			<div class="col-xs-12">
				<div class="row header-top">
					<div class="col-sm-12 col-md-10 col-md-offset-1">
						<div class="social">Follow - Like - Donate</div>
						<div class="logo"><a href='/'>ContactMyReps.org</a></div>
						<div class="links text-right text-uppercase">
							<a href='/about'>About Us</a> | <a href='/contact'>Contact Us</a><br>
							<a href='/organizations'>Organizations</a> | <a href='/terms'>Terms of Service</a>
						</div>
					</div>
				</div>
				<div class="row header-bottom">
					<div class="col-sm-12 col-md-10 col-md-offset-1 search">
						<div class="row">
							<div class="col-sm-10 col-sm-offset-1">
								<h4 class="text-center">Enter your Zip Code, Address, State, or Representative's Name. Discover who represents you and contact them via email or social media. This is your government and you can only make a difference by making your voice heard.</h4>
							</div>
						</div>
						<div class="row">
							<form v-on:submit="search" class="col-xs-6 col-xs-offset-1 col-md-7">
								<div class="input-group">
									<input type="text" id="input" v-model="query | search" class="form-control">
									<span class="input-group-btn"><button class="btn btn-default" type="button"><i class="fa fa-search"></i></button></span>
								</div>
								<p class="help-block">Popular searches: <a href='/90210'>90210</a>, <a href='/Virginia'>Virginia</a>, Niccki Haley</p>
							</form>
							<div class="col-xs-4 col-md-3">
								<span class="hidden-xs pr30">OR</span>
								<button type="button" class="enable-geo btn btn-primary" v-on:click="locate">Use My Location <i class="fa fa-map-marker"></i></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	<!--
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
		</div> -->

		<div class="row location">
			<div class="col-sm-12 text-center">
				<h4 v-show="hasResults">
					Search Results for 
					<span class="zip" v-if="zip" v-text="zip"></span> - 
					<span class="city" v-if="city" v-text="city"></span>, 
					<span class="state" v-if="state" v-text="state"></span>
				</h4>
			</div>
		</div>
<!-- 	</div>
<div class="container"> -->
		<div class="row results">
		  	<div class="col-sm-6 col-sm-offset-3" v-show="loading">
				<div class="progress progress-striped active">
				  <div class="progress-bar" style="width: 100%"></div>
				</div>
	  		</div>
			<div class="col-sm-12" v-show="!loading">
				<item v-for="item in reps" :item="item" :role="role"></item>
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
</div>
@stop

@section('scripts.body')
	<script src="{{ URL::asset('js/main.js') }}"></script>
@stop