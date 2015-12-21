@extends('layout')

@section('content')

<form id='fm'>
	<input type='text' id='zipcode' placeholder='Enter Your Zip Code' required pattern="\d{5}([\-]?\d{4})?">
</form>

@stop

@section('scripts.body')

	<script src="{{ URL::asset('js/main.js') }}"></script>

@stop