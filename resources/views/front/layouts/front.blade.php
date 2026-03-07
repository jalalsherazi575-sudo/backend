<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name', 'Sytadel App') }}</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,700' rel='stylesheet' type='text/css'>
    <link href="{{mix("/assets/front/css/front.css")}}" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @include('front.layouts.partials.favicons')
    @yield('styles')
</head>
<body>

@include('front.layouts.partials.laraspace-notifs')
@include('front.layouts.partials.header-front')

@yield('content')

@include('front.layouts.partials.footer')

<script src="{{mix('/assets/front/js/plugins.js')}}"></script>
<script type="text/javascript">
        $(document).ready(function () {
            var isiDevice = /ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase());
            //            if (isiDevice) {
            //                $(".top").addClass('showIcon');
            //            }

            var isiDevice = /ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase());
            var isAndroid = /android/i.test(navigator.userAgent.toLowerCase());
            if (isiDevice) {
                jQuery("#apple").show();
                jQuery("#android").hide();
            }
            else if (isAndroid) {
                jQuery("#apple").hide();
                jQuery("#android").show();
            }
            else {
            }
        });
    </script>
@yield('scripts')
</body>
</html>