@extends('layout')

@section('content')
<div id="home">
	<div class="container-fluid">
		<div class="row header">
			<div class="col-xs-12">
				<div class="row header-top">
					<div class="col-sm-12 col-md-10 col-md-offset-1">
						<div class="banner-style"><img class="twitter-style" src="/images/twitter-button.png"/><img src="/images/fb-like-button.png"/></div>
						<div class="logo"><a href='/'>
							<img class="logo-style" src="/images/logo.png"/></a></div>
						<div class="links text-right text-uppercase" style="font-family:'Arial'">
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
									<span class="input-group-btn">
										<button class="btn btn-default" type="button" v-on:click="search"><i class="fa fa-search"></i></button>
									</span>
								</div>
								<p class="help-block">Popular searches: <a href='/90210'>90210</a>, <a href='/Virginia'>Virginia</a>, <a href='/Nikki Haley'>Nikki Haley</a></p>
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

		<div class="row location" v-show="hasResults">
			<div class="col-sm-12 text-center">
				<h4>
					<span v-text="printSearch"></span>
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
				<item v-for="item in reps" :item="item" :role="role"></item>
			</div>
		</div>

		@if (Auth::check())
			<input type="hidden" id="role" value="{{ Auth::user()->id }}">
		@endif

	</div>
</div>
@stop

@section('scripts.body')
	@if (isset($location))
		<script>var ipinfo = {!! json_encode($location) !!}</script>
	@endif

	<script src="{{ URL::asset('js/home.js') }}"></script>
@stop
