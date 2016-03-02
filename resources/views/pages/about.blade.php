@extends('layout')

@section('content')

<div id="about" class="container-fluid page">

	@include('partials/_nav')

	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h2>ABOUT US</h2>
				<hr>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				@include('markdown/about')
			</div>
		</div>
	</div>

</div>
@stop

@section('scripts.body')
	<script>
		$('.enable-geo').click(function(){
			window.location = '/me';
		});
		$('form').submit(function(e){
			e.preventDefault();
			window.location = '/'+$('#input').val();
		});
	</script>
@stop
