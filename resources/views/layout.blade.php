<!DOCTYPE html>
<html lang="en">
<head>
	<title>Contact My Reps - @yield('title')</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="@yield('description')">

	<link rel="stylesheet" href="/css/app.css" />

	@yield('scripts.head')

</head>
<body>

@yield('nav')

@yield('content')

@yield('sidebar')

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

@yield('scripts.body')

</body>
</html>