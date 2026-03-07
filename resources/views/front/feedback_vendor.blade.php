@extends('front.layouts.contactus')
@section('scripts')
    <script src="/assets/front/js/contactus/validation.js"></script>
@stop

@section('content')
    <section class="section section-hero-area webpage">
        <div class="container text-sm-center">
             <div class="row">
       <div class="col-sm-6">	
        <div class="card center">
            
            <div class="card-body">
            	<p>Tell us what you love about the app, or what we could be doing better.</p>
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('feedback_vendor') }}" name="Feedback" novalidate>
                 @csrf
                 <input type="hidden" name="vendorId" id="vendorId" value="@if(isset($vendorId)){{$vendorId}}@endif">
                 <input type="hidden" name="customerId" id="customerId" value="@if(isset($customerId)){{$customerId}}@endif">

                      <div class="form-group">
                        <textarea name="message" rows="10" required="required"  class="form-control" id="message" placeholder="Enter Feedback"></textarea>
                        
                    </div>
                    <p>If you have something to say that doesn't fit here, please shoot us an email at <a href="mailto:help@quickserv.com" target="_new">help@quickserv.com</a></p>
                    <input type="submit" name="submit" id="submit" class="btn btn-primary"  value="Send Feedback">
                    <!-- <button class="btn btn-primary">Submit</button> -->

                </form>	
             </div>
         </div>
        </div>
       </div>  
     </div> 
    </section>
@endsection