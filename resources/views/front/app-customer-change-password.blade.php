@extends('front.layouts.contactus')
@section('scripts')
    <script>
    // jQuery code to remove messages after 5 seconds
    $(document).ready(function() {
        // Select the message container
        var messageContainer = $('#message-container');

        // Set a timeout to remove the messages after 5 seconds
        setTimeout(function() {
            // Empty the message container
            messageContainer.empty();
        }, 5000); // 5000 milliseconds = 5 seconds
    });
</script>
@stop
@section('content')
    <section class="section section-hero-area webpage">
        <div class="container text-sm-center">
             <div class="row">
       <div class="col-sm-6 "> 
        <div class="card center">
            
            <div class="card-body">
               <div id="message-container">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                </div>
               <!--  <h3 class="card_body_title">Change Password</h3> -->
              <p>{{$title}}</p>
                <form action="{{url('/changePassword/')}}" id="changepassword" method="post">

                 {{csrf_field()}}
                 <input type="hidden" name="customerId" id="customerId" value="{{$customerid}}">
                         <div class="form-group">
                            <input type="password" maxlength="50" class="form-control @error('current_password') is-invalid @enderror" required="required" value="{{old('current_password')}}" id="current_password" name="current_password" placeholder="{{$current_password}}">
                            @error('current_password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                         <div class="form-group">
                            <input type="password" maxlength="50" class="form-control @error('new_password') is-invalid @enderror" required="required" id="new_password" value="{{old('new_password')}}" name="new_password" placeholder="{{$new_password}}">
                            @error('new_password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                           <input id="new_password_confirmation" type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" name="new_password_confirmation" required autocomplete="new-password"  placeholder="{{$confirm_password}}">
                            @error('new_password_confirmation')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <input type="submit" name="submit" id="submit" class="btn btn-primary"  value="{{$send_message}}">
                   </form> 
        </div>
    </section>
@endsection