<!DOCTYPE html>
<html lang="en">
<head>
	<title>Contact My Reps</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="@yield('description')">

  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="/css/app.css" />

	@yield('scripts.head')

</head>
<body>

@yield('nav')

@yield('content')

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>

@yield('scripts.body')

</body>
</html>