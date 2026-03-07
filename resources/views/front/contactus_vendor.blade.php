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
            	<p>We would love to hear from you!</p>
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('contact_us_vendor') }}" name="country" novalidate>
                 {{csrf_field()}}
                 <div class="form-group">
                        <input type="text" maxlength="50" class="form-control" required="required" value="@if(isset($contactname)){{$contactname}}@endif" id="contactname" name="contactname" placeholder="Contact Name">
                    </div>
                     <div class="form-group">
                        <input type="email" maxlength="50" class="form-control" required="required" id="contactemail" value="@if(isset($contactemail)){{$contactemail}}@endif" name="contactemail" placeholder="Contact Email">
                    </div>
                    <div class="form-group">
                        <input type="text" maxlength="50" class="form-control" required="required" id="contactphone" value="@if(isset($contactphone)){{$contactphone}}@endif" name="contactphone" placeholder="Contact Phone Number">
                    </div>
                     <div class="form-group">
                    <select name="howcanwehelpyou" class="form-control" tabindex="-1" aria-hidden="true"><option value="howcanwehelpyou">How can we help you?</option>
                    </select>
                      </div>

                      <div class="form-group">
                        <textarea name="message" class="form-control" id="message" placeholder="Message"></textarea>
                        
                    </div>
                    <div class="form-group">
                    	<label style="float:left;">Upload Photo </label> 
                    	<input type="file" name="photo" placeholder="Photo" class="form-control-file">
                    </div>
                    <input type="submit" name="submit" id="submit" class="btn btn-primary"  value="Send Message">
                    <!-- <button class="btn btn-primary">Submit</button> -->

                </form>	
        </div>
    </section>
@endsection