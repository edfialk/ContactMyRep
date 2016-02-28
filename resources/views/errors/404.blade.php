<!DOCTYPE html>
<html>
    <head>
        <title>Be right back.</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
            .links {
            	font-size: 48px;
            	margin-bottom: 30px;
            }
            .links a {
            	text-decoration: none;
            }
            .links a + a:before {
            	content: " - ";
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Oh no, there's no page there! Try these:</div>
                <div class="links">
                	<a href='/'>Home</a>
                	<a href='about'>About</a>
                	<a href='contact'>Contact</a>
                </div>
            </div>
        </div>
    </body>
</html>
