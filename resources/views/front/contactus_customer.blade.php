@extends('front.layouts.contactus')
@section('scripts')
    <script src="/assets/front/js/contactus/validation.js"></script>
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

<?php 
$common = new Laraspace\Http\Controllers\CommanController;
?>

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
             <!--    <h3 class="card_body_title">Contact Us</h3> -->
              <p>{{ $common->get_msg('cntct_toplabel',1) }}</p>
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('contact_us_customer') }}" name="country" novalidate>
                 {{csrf_field()}}
                 <input type="hidden" name="customerId" id="customerId" value="@if(isset($customerId)){{$customerId}}@endif">

                         <div class="form-group">
                            <input type="text" maxlength="50" class="form-control" required="required" value="{{old('contactname')}}" id="contactname" name="contactname" placeholder="{{ $common->get_msg('cntct_contact_name',1) }}">
                        </div>

                         <div class="form-group">
                            <input type="email" maxlength="50" class="form-control" required="required" id="contactemail" value="{{old('contactemail')}}" name="contactemail" placeholder="{{ $common->get_msg('cntct_contact_email',1) }}">
                        </div>

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control" required="required" id="contactphone" value="{{old('contactphone')}}" name="contactphone" placeholder="{{ $common->get_msg('cntct_contact_phone_number',1) }}">
                        </div>
                    

                        <div class="form-group">
                          <textarea name="message" class="form-control" id="message" placeholder="{{ $common->get_msg('cntct_contact_message',1) }}">{{old('message')}}</textarea>
                        </div>

                        

                    <input type="submit" name="submit" id="submit" class="btn btn-primary"  value="{{ $common->get_msg('cntct_button_label',1) }}">
                    <!-- <button class="btn btn-primary">Submit</button> -->

                </form> 
        </div>
    </section>
@endsection