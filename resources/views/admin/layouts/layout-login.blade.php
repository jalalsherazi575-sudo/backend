<!DOCTYPE html>
<html>
<head>
    <title>Medfellows App</title>
    <link href="{{ mix('/assets/admin/css/laraspace.css') }}" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('admin.layouts.partials.favicons')
</head>
<body class="login-page login-1">
<div id="app" class="login-wrapper">
    <div class="login-box">
        @include('admin.layouts.partials.laraspace-notifs')
        @include('admin.layouts.partials.flash-message')
        <div class="logo-main">
            <a href="javascript:void(0);" title="Medfellows"><img src="/assets/admin/img/logo.svg"  style="text-align: center;" alt="Medfellows App" title="Medfellows"></a>
            <!-- <a href="javascript:void(0);" title="GrapeVine App"><img src="/assets/admin/img/logo-3.png" width="100px" style="width:70px !important" alt="GrapeVine App" title="GrapeVine App"></a>
            <br><br>GrapeVine App -->
        </div>
        @yield('content')
        <div class="page-copyright">
            
            <p>Medfellows App © {{ date('Y') }}</p>
        </div>
    </div>
</div>
<script src="{{mix('/assets/admin/js/core/plugins.js')}}"></script>
<script src="{{mix('/assets/admin/js/core/app.js')}}"></script>
@yield('scripts')
</body>
</html>
