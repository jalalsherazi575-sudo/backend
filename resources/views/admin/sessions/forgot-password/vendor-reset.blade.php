@extends('admin.layouts.layout-login')

@section('scripts')
    <script src="/assets/admin/js/sessions/login.js"></script>
@stop

@section('content')
    @if(isset($token))
	<p style="font-size:18px;">To complete reset password process, please enter your new password.</p>
    <form action="{{url('/resetpassword/')}}" id="ResetPassword" method="post">
        {{csrf_field()}}
        <input type="hidden" name="id" value="{{$token}}">
        
        <div class="form-group">
            <input type="password" class="form-control form-control-danger" placeholder="Enter password" name="password">
        </div>
        <div class="form-group">
            <input type="password" class="form-control form-control-danger" placeholder="Enter Confirm password"
                   name="password_confirmation">
        </div>
        <button class="btn btn-theme btn-full">Reset Password</button>
    </form>
	@else
	<p style="font-size:18px;">Your Password has been reset successfully. you can login now.</p>	
	@endif
	
@stop
