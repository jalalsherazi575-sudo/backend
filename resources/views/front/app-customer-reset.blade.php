@extends('admin.layouts.layout-login')

@section('scripts')
    <script src="/assets/admin/js/sessions/login.js"></script>
@stop

@section('content')
    <form action="{{url('/appresetpasswordcustomer/')}}" id="ResetPassword" method="post">
        {{csrf_field()}}
        <input type="hidden" name="token" value="{{$token}}">
        
        <div class="form-group">
            <input type="password" class="form-control form-control-danger @error('password') is-invalid @enderror" placeholder="Enter password" name="password">
            @error('password')
                <span class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="password" class="form-control form-control-danger @error('password_confirmation') is-invalid @enderror" placeholder="Enter Confirm password"
                   name="password_confirmation">
            @error('password_confirmation')
                <span class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <button class="btn btn-theme btn-full">Reset Password</button>
    </form>
@stop
