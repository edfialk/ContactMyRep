@extends('layout')

@section('content')
<div class="container">

	<div class="row logo"></div>
	<div class="row search">
		<form v-on:submit="search" class="col s12 m10 offset-m1">
			<div class="row">
				<div class="input-field col s12">
					<input type="text" id="input" v-model="query | search" lazy placeholder="Zip code or address">
				</div>
				<div class="status-wrapper col s12 center-align" v-show="status.length > 0" transition="fade" style="display: none;">
					<span class="status red accent-1 z-depth-1" v-text="status"></span>
				</div>
				<div class="input-field col s12 center-align">
					<input type="submit" class="waves-effect waves-light btn" value="Search">
					<input type="button" class="enable-geo waves-effect waves-light btn" value="Use my location" v-on:click="locate">
				</div>

			</div>
		</form>
	</div>

	<div class="row location center-align">
		<span class="city" v-if="city" v-text="city"></span>
		<span class="state" v-if="state" v-text="state"></span>
		<span class="zip" v-if="zip" v-text="zip"></span>
	</div>

	<hr v-show="!loading">

	<div class="row results">
	  	<div class="col s6 offset-s3 progress" v-show="loading">
      		<div class="indeterminate"></div>
  		</div>
		<div class="col s12" v-show="!loading">
			<table>
				<tbody>
					<tr is="item" v-for="item in reps" :item="item"></tr>
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

</div>
@stop

@section('scripts.body')
	<script type="text/javascript"
	        src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1'}]}">
	</script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBD-WBhPqQZUEuGI8HBFOiVXtT6q9Xt5qc">
    </script>
	<script src="{{ URL::asset('js/main.js') }}"></script>
@stop