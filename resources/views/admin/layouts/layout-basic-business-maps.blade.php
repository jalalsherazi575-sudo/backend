<!DOCTYPE html>
<html>
<head>
    <title>Sytadel App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,700' rel='stylesheet' type='text/css'>
    <script src="{{asset('/assets/admin/js/core/pace.js')}}"></script>
    <link href="{{ mix('/assets/admin/css/laraspace.css') }}" rel="stylesheet" type="text/css">
    <!-- <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen"
     href="http://tarruda.github.com/bootstrap-datetimepicker/assets/css/bootstrap-datetimepicker.min.css"> -->
    <!-- <link href="{{ asset('/assets/admin/css/bootstrap-combined.min.css') }}" rel="stylesheet" type="text/css"> -->
    <link href="{{ asset('/assets/admin/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('/assets/admin/css/jquery.fancybox.css') }}" rel="stylesheet" type="text/css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('admin.layouts.partials.favicons')
    @yield('styles')

    </head>

<body class="layout-default skin-default" onload="initAutocomplete()">
    @include('admin.layouts.partials.laraspace-notifs')

    <div id="app" class="site-wrapper">
        @include('admin.layouts.partials.businessheader')
        <div class="mobile-menu-overlay"></div>
        @include('admin.layouts.partials.businesssidebar',['type' => 'default'])

        @yield('content')

        @include('admin.layouts.partials.footer')
        @if(config('laraspace.skintools'))
            @include('admin.layouts.partials.skintools')
        @endif
    </div>
      <!-- <script type="text/javascript"
     src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js">
    </script> 
    <script type="text/javascript"
     src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js">
    </script>
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js">
    </script>
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.pt-BR.js">
    </script>   --> 

    <script src="{{asset('/assets/admin/js/jquery.min.js')}}"></script>
    <script src="{{asset('/assets/admin/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('/assets/admin/js/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="{{asset('/assets/admin/js/bootstrap-datetimepicker.pt-BR.js')}}"></script>

    <script src="{{mix('/assets/admin/js/core/plugins.js')}}"></script>
    <script src="{{asset('/assets/admin/js/demo/skintools.js')}}"></script>
    <script src="{{mix('/assets/admin/js/core/app.js')}}"></script>
    <script src="{{asset('/assets/admin/js/jquery.fancybox.js')}}"></script> 
      
<script>   

jQuery( document ).ready(function() {
jQuery( ".side-nav.metismenu > li > ul" ).before( '<span class="accordion-toggle"></span>' );
	jQuery(".side-nav.metismenu li span.accordion-toggle").click(function () { 
	
		//$(this).removeAttr('href');
		var element = jQuery(this).parent('li');
		
		if (element.hasClass('open')) {
			element.removeClass('open');
			element.find('li').removeClass('open');
			element.find('ul').slideUp();
		}
		else {
			element.addClass('open');
			element.children('ul').slideDown();
			element.siblings('li').children('ul').slideUp();
			element.siblings('li').removeClass('open');
			element.siblings('li').find('li').removeClass('open');
			element.siblings('li').find('ul').slideUp();
		}
	});
    jQuery(".gallery").fancybox();
}); 
    </script>

    @yield('scripts')
</body>
</html>
