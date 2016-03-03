<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title', 'Contact My Reps')</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="@yield('description')">
	<meta name="google-site-verification" content="AE_XVi6xkk5SCzCBGF_nUkmWOW5GNZRnB7IaaRLPxPI" />

  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Bree+Serif' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="/css/app.css">
	<link rel="stylesheet" href="/css/omar.css">

	@yield('scripts.head')

</head>
<body>

@yield('nav')

@yield('content')

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js'></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

@yield('scripts.body')

</body>
</html>