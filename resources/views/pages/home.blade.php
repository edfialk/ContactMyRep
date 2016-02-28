@extends('layout')

@section('scripts.head')
	<link href='https://fonts.googleapis.com/css?family=Bree+Serif' rel='stylesheet' type='text/css'>
@stop

@section('content')

@include('social')

<div id="home" class="container-fluid">
	<div class="row header">
		<div class="header-top col-xs-12">
			<div class="container">
				<div class="row">
					<div class="social visible-xs">
						<a href="https://facebook.com/ContactMyReps"><img src="/images/fb-like-button-small.png"></a>
						<a href="https://twitter.com/ContactMyReps" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow</a>
					</div>
					<div class="col-sm-6">
						<div class="logo">
							<a href='/'><img class="logo-style" src="/images/logo.png"/></a>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="social hidden-xs">
							<a href="https://facebook.com/ContactMyReps"><img src="/images/fb-like-button-small.png"></a>
							<a href="https://twitter.com/ContactMyReps" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow</a>
						</div>
						<div class="links text-right text-uppercase" style="font-family:'Arial'">
							<a href='/about'>About Us</a> | <a href='/contact'>Contact Us</a><br>
							<a href='/organizations'>Organizations</a> | <a href='/terms'>Terms of Service</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="header-bottom col-xs-12">
			<div class="container">
				<div class="row">
					<div class="col-sm-12 col-md-10 col-md-offset-1 search">
						<div class="row">
							<div class="col-xs-12">
								<h4 class="mission-statement text-center">
									Enter your Zip Code, Address, State, or Representative's Name. <br class="visible-md">Discover who represents you<br class="visible-lg">
									and contact them by email or social media. <br class="visible-md">This is your government and<br class="visible-lg">
									you can only make a difference by making your voice heard.
								</h4>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4 col-sm-push-7">
								<span class="hidden-xs pr30">OR</span>
								<button type="button" class="enable-geo btn btn-primary" v-on:click="locate">Use My Location <i class="fa fa-map-marker"></i></button>
							</div>
							<form v-on:submit="search" class="col-sm-6 col-sm-pull-4 col-sm-offset-1">
								<div class="input-group">
									<input type="text" id="input" v-model="query | search" class="form-control" placeholder="Search Zip Code">
									<span class="input-group-btn">
										<button class="btn btn-default" type="button" v-on:click="search"><i class="fa fa-search"></i></button>
									</span>
								</div>
								<p class="help-block">Popular searches: <a href='/90210'>90210</a>, <a href='/Virginia'>Virginia</a>, <a href='/Nikki Haley'>Nikki Haley</a></p>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row loading" v-show="loading">
		@include('loader')
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
