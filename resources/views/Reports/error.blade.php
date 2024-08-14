<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>{{ settings('name') }}</title>
    <style>
        @import url("https://fonts.googleapis.com/css?family=Cabin+Sketch");

        html {
            height: 100%;
        }

        body {
            min-height: 100%;
        }

        body {
            display: flex;
        }

        h1 {
            font-family: "Cabin Sketch", cursive;
            font-size: 3em;
            text-align: right;
            opacity: 0.8;
            order: 1;
        }

        h1 small {
            display: block;
        }

        .site {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            flex-direction: row;
            margin: 0 auto;
            gap: 50px;
        }
    </style>
</head>

<body>
    <div class="site">
        <div class="sketch">
            <img src="{{ asset('public/logo.png') }}" style="width: 196px;">
        </div>

        <h1>ERROR:
            <small>{{$message}}</small>
        </h1>
    </div>
</body>

</html>
