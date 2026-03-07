<!DOCTYPE html>
<html>
<head>
    <title>Quick Serv</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,700' rel='stylesheet' type='text/css'>
    <link href="{{mix("/assets/front/css/front.css")}}" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @include('front.layouts.partials.favicons')
    @yield('styles')
</head>
<body class="layout-default skin-default">

@yield('content')

@include('front.layouts.partials.footer')

<script src="{{mix('/assets/front/js/plugins.js')}}"></script>
<script>
document.querySelector("button").onclick = function() {
  console.log("Send post message");
  window.postMessage("Hello React");
}
</script>
@yield('scripts')
</body>
</html>