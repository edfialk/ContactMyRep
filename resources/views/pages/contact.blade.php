@extends('layout')

@section('content')

<div id="contact" class="container-fluid page">

	@include('partials/_nav')

	<div class="container">
		<div class="row">
			<div class="col-md-9">
				@if (session('success'))
					<div class="alert alert-success">
						{{ session('success') }}
					</div>
				@endif
				<h2>CONTACT US</h2>
				<hr>
				<form action="/contact" method="post">
				{{ csrf_field() }}
				<div class="form-group col-md-6">
					<label for="name">Name</label>
					<input type="text" class="form-control" id="name" placeholder="Name">
				</div>
				<div class="form-group col-md-6">
					<label for="email">Email address</label>
					<input type="email" class="form-control" id="email" placeholder="Email">
				</div>
				<div class="form-group col-xs-12">
					<label for="message">Message</label>
					<textarea class="form-control" rows="10"></textarea>
				</div>
				<div class="form-group col-xs-12">
					<button class="btn btn-block btn-primary">SEND</button>
				</div>
			</div>
			<div class="col-md-3 sidebar-right">
				<div class="row">
					<div class="col-xs-12">
						<span class="contact-label">email</span>
						<span class="contact-text"><a href='mailto:admin@contactmyreps.org'>admin@contactmyreps.org</a></span>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<a href='http://facebook.com/contactmyreps'><img src='/images/fb-art.jpg'></a>
						<a href='http://twitter.com/contactmyreps'><img src='/images/twitter-art.png'></a>
					</div>
				</div>
				<div class="row">
				</div>
			</div>
		</div>
	</div>

</div>
@stop