@extends('front.layouts.contactus')
@section('scripts')
    <script src="/assets/front/js/contactus/validation.js"></script>
@stop
    <?php 
    $common = new Laraspace\Http\Controllers\CommanController;
    ?>

@section('content')
    

    <section class="section section-hero-area webpage">
        <div class="container text-sm-center">
             <div class="row">
       <div class="col-sm-6">   
        <div class="card center">
            
            <div class="card-body">
                <p>{{ $common->get_msg('pay_dtl_toplabel',1) }}</p>
                
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('customerupdatepayment') }}" name="Feedback">
                 @csrf
                 
                 <input type="hidden" name="customerId" id="customerId" value="@if(isset($customerId)){{$customerId}}@endif">
                 <input type="hidden" name="tpayurl" value="{{$tpayurl}}">
                        <!-- @if(empty($lastname))
                            <div class="form-group">
                                <input type="text" maxlength="50" class="form-control  @error('lastname') is-invalid @enderror" required="required" value="@if(isset($lastname)){{$lastname}}@endif" id="lastname" name="lastname" placeholder="Last Name">
                                @if ($errors->has('lastname'))
                                    <span class="error">
                                        <strong>{{ $errors->first('lastname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        @endif -->

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('company_name') is-invalid @enderror" id="company_name" value="@if(isset($company_name)){{$company_name}}@endif" name="company_name" placeholder="{{ $common->get_msg('pay_dtl_company_name',1) }}">
                             @if ($errors->has('company_name'))
                                <span class="error">
                                    <strong>{{ $errors->first('company_name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('tax_code') is-invalid @enderror" required="required" id="tax_code" value="@if(isset($tax_code)){{$tax_code}}@endif" name="tax_code" placeholder="Tax Code">
                             @if ($errors->has('tax_code'))
                                <span class="error">
                                    <strong>{{ $errors->first('tax_code') }}</strong>
                                </span>
                            @endif
                        </div> -->

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('invoice_fname') is-invalid @enderror" required="required" id="invoice_fname" value="@if(isset($invoice_fname)){{$invoice_fname}}@endif" name="invoice_fname" placeholder="{{ $common->get_msg('pay_dtl_first_name',1) }}">
                             @if ($errors->has('invoice_fname'))
                                <span class="error">
                                    <strong>{{ $errors->first('invoice_fname') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('invoice_lname') is-invalid @enderror" required="required" id="invoice_lname" value="@if(isset($invoice_lname)){{$invoice_lname}}@endif" name="invoice_lname" placeholder="{{ $common->get_msg('pay_dtl_last_name',1) }}">
                             @if ($errors->has('invoice_lname'))
                                <span class="error">
                                    <strong>{{ $errors->first('invoice_lname') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('nip') is-invalid @enderror" id="nip" value="@if(isset($nip)){{$nip}}@endif" name="nip" placeholder="{{ $common->get_msg('pay_dtl_nip',1) }}">
                             @if ($errors->has('nip'))
                                <span class="error">
                                    <strong>{{ $errors->first('nip') }}</strong>
                                </span>
                            @endif
                        </div>


                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('flat_number') is-invalid @enderror" required="required" id="flat_number" value="@if(isset($flat_number)){{$flat_number}}@endif" name="flat_number" placeholder="{{ $common->get_msg('pay_dtl_flat_number',1) }}">
                             @if ($errors->has('flat_number'))
                                <span class="error">
                                    <strong>{{ $errors->first('flat_number') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('street_number') is-invalid @enderror" required="required" id="street_number" value="@if(isset($street_number)){{$street_number}}@endif" name="street_number" placeholder="{{ $common->get_msg('pay_dtl_street_number',1) }}">
                             @if ($errors->has('street_number'))
                                <span class="error">
                                    <strong>{{ $errors->first('street_number') }}</strong>
                                </span>
                            @endif
                        </div>

                         <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('street') is-invalid @enderror" required="required" id="street" value="@if(isset($street)){{$street}}@endif" name="street" placeholder="{{ $common->get_msg('pay_dtl_street',1) }}">
                             @if ($errors->has('street'))
                                <span class="error">
                                    <strong>{{ $errors->first('street') }}</strong>
                                </span>
                            @endif
                        </div>
                        

                        
                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('city') is-invalid @enderror" required="required" id="city" value="@if(isset($city)){{$city}}@endif" name="city" placeholder="{{ $common->get_msg('pay_dtl_city',1) }}">
                             @if ($errors->has('city'))
                                <span class="error">
                                    <strong>{{ $errors->first('city') }}</strong>
                                </span>
                            @endif
                        </div>
                        
                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('country') is-invalid @enderror" required="required" id="country" value="@if(isset($country)){{$country}}@endif" name="country" placeholder="{{ $common->get_msg('pay_dtl_country',1) }}">
                             @if ($errors->has('country'))
                                <span class="error">
                                    <strong>{{ $errors->first('country') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('post_code') is-invalid @enderror" required="required" id="post_code" value="@if(isset($post_code)){{$post_code}}@endif" name="post_code" placeholder="{{ $common->get_msg('pay_dtl_post_code',1) }}">
                             @if ($errors->has('post_code'))
                                <span class="error">
                                    <strong>{{ $errors->first('post_code') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- <div class="form-group">
                            <input type="text" maxlength="50" class="form-control  @error('servicename') is-invalid @enderror" required="required" id="servicename" value="@if(isset($servicename)){{$servicename}}@endif" name="servicename" placeholder="Service name">
                             @if ($errors->has('servicename'))
                                <span class="error">
                                    <strong>{{ $errors->first('servicename') }}</strong>
                                </span>
                            @endif
                        </div> -->
                    
                    <input type="submit" name="submit" id="submit" class="btn btn-primary"  value="{{ $common->get_msg('pay_dtl_button_label',1) }}">
                    <!-- <button class="btn btn-primary">Submit</button> -->

                </form> 
             </div>
         </div>
        </div>
       </div>  
     </div> 
    </section>
@endsection