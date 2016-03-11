<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title', 'Contact My Representatives')</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="@yield('description', 'A comprehensive government tracking website aimed at providing every possible contact channel for U.S. representatives in government. Search by zipcode, address, city, or name to find and contact your representatives.')">
	<meta name="google-site-verification" content="AE_XVi6xkk5SCzCBGF_nUkmWOW5GNZRnB7IaaRLPxPI" />
	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="@contactmyreps" />
	<meta name="twitter:title" content="A Channel for Your Voice" />
	<meta name="twitter:description" content="Every possible contact channel for U.S. representatives in the highest levels of government." />
	<meta name="twitter:image" content="http://contactmyreps.orgv/images/logo.png" />
	<meta property="og:title" content="Contact My Reps" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://contactmyreps.org" />
	<meta property="og:image" content="http://contactmyreps.org/images/logo.png" />
	<meta name="csrf-token" content="{{ csrf_token() }}" />

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