@extends('admin.layouts.layout-basic')
@section('scripts')
<!-- <script @if(isset($customer)) src="/assets/admin/js/customerregister/validation.js" @else src="/assets/admin/js/customerregister/validation.js" @endif ></script>
   -->
<script>
   $(function() {
       var today = new Date();
       $( ".ls-datepicker" ).datepicker(
           {
             changeMonth: true,
             dateFormat: 'dd.mm.yy',
             changeYear: true,
             maxDate: '-1D',
             yearRange: "-116:+0"
           }
       );
   });
</script>
@stop
@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($customer)) Edit @else Add @endif customer</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/customers')}}">Customers</a></li>
            <li class="breadcrumb-item active">@if(isset($customer)) Edit @else Add @endif Customer</li>
        </ol>
    </div> <!-- header -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card center">
                <div class="card-body">
                    <form id="customerform" enctype="multipart/form-data" method="post" action="@if(isset($customer)){{ URL::to('admin/customer/update/'.$customer->id.'')}}@else{{ URL::to('admin/customer/store') }}@endif" name="country" novalidate>
                        {{csrf_field()}}
                        <div class="form-group">
                            <label>Full Name <span class="req">*</span></label>
                            <input type="text" maxlength="50" class="form-control  @error('name') is-invalid @enderror"  value="@if(isset($customer)){{$customer->name}}@endif" name="name"
                             placeholder="First Name">
                            @if ($errors->has('name'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('name') }}
                                </span>
                            @endif
                        </div> <!-- name -->
                        <div class="form-group">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" maxlength="50" class="form-control @error('email') is-invalid @enderror" value="@if(isset($customer)){{$customer->email}}@endif" name="email" placeholder="Email">
                            @if ($errors->has('email'))
                                <span class="error help-block" style="color:red;">
                                    {{ $errors->first('email') }}
                                </span>
                            @endif
                        </div><!-- Email -->
                        <div class="form-group">
                            <label>Password <span class="req">*</span></label>
                            <input type="password" maxlength="20" class="form-control @error('password') is-invalid @enderror" value="" name="password" placeholder="Password">
                            @if ($errors->has('password'))
                                <span class="error help-block" style="color:red;">
                                    {{ $errors->first('password') }}
                                </span>
                            @endif
                        </div><!-- Password -->
                        <div class="form-group">
                            <label>Phone <span class="req">*</span></label>
                            <input type="text" maxlength="13" class="form-control @error('phone') is-invalid @enderror" value="@if(isset($customer)){{$customer->phone}}@endif" name="phone" placeholder="Phone">
                            @if ($errors->has('phone'))
                                <span class="error help-block" style="color:red;">
                                    {{ $errors->first('phone') }}
                                </span>
                            @endif
                        </div> <!-- Phone -->
                        <div class="form-group">
                            <label>Status</label>
                            <select name="isActive" class="form-control ls-select2">
                                <option value="1" @if(isset($customer) && $customer->isActive==1) selected @endif>Active</option>
                                <option value="0" @if(isset($customer) && $customer->isActive==0) selected @endif>Inactive</option>
                            </select>
                        </div><!-- Status -->
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" value="@if(isset($customer)){{$customer->photo}}@endif" name="photo" accept="image/*"><br>
                            @if ($errors->has('photo'))
                                <span class="error help-block" style="color:red;">
                                    {{ $errors->first('photo') }}
                                </span>
                            @endif
                            <br>
                            @if (isset($customer->photo) && $customer->photo != '' && file_exists(public_path('customerregisterphoto/thumbnail_images/' . $customer->photo)))
                                <img src="{{URL::to('customerregisterphoto/thumbnail_images/'.$customer->photo)}}">
                                <span>Preferred Size: {{$customerimagewidth}}px <strong>X</strong> {{$customerimageheight}}px(w x h)</span>
                            @endif
                        </div><!-- Photo -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Created Date :</label>
                                @if($customer->createdDate!='') 
                                    {{$customer->createdDate}} 
                                @endif                    
                            </div>
                        @endif<!-- Created Date -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Last Login Date :</label>
                                @if($customer->lastLoginDate!='')
                                    {{$customer->lastLoginDate}}
                                @endif                     
                            </div>
                        @endif <!-- Last Login Date -->


                        @if(isset($customer))
                            <div class="form-group">
                                <label>Invoice First Name :</label>
                                @if($customer->invoice_fname!='')
                                    {{$customer->invoice_fname}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Invoice Last Name :</label>
                                @if($customer->invoice_lname!='')
                                    {{$customer->invoice_lname}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Street :</label>
                                @if($customer->street!='')
                                    {{$customer->street}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Street Number :</label>
                                @if($customer->street_number!='')
                                    {{$customer->street_number}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Flat Number :</label>
                                @if($customer->flat_number!='')
                                    {{$customer->flat_number}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>City :</label>
                                @if($customer->city!='')
                                    {{$customer->city}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Post Code :</label>
                                @if($customer->post_code!='')
                                    {{$customer->post_code}}
                                @endif                     
                            </div>
                        @endif
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Country :</label>
                                @if($customer->country!='')
                                    {{$customer->country}}
                                @endif                     
                            </div>
                        @endif



                        @if(isset($customer))
                            <div class="form-group">
                                <label>Login Status :</label>
                                @if($customer->loginStatus==1)
                                    Yes
                                @else
                                    No
                                @endif                    
                            </div>
                        @endif <!-- Login Status -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Device Type :</label>
                                @if($customer->deviceType==1)
                                    Android
                                @elseif($customer->deviceType==2)
                                    Iphone
                                @else
                                @endif                    
                            </div>
                        @endif <!-- Device Type -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Device Details :</label>
                                @if($customer->deviceDetails!='')
                                    {{$customer->deviceDetails}}
                                @endif            
                            </div>
                        @endif <!-- Device Details -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>Device Token :</label>
                                @if($customer->deviceToken!='')
                                    {{$customer->deviceToken}}
                                @endif                    
                            </div>
                        @endif <!-- Device Token -->
                        @if(isset($customer))
                            <div class="form-group">
                                <label>socialMediaType :</label>
                                @if($customer->loginType==1)
                                    App
                                @elseif($customer->loginType==2)
                                    Facebook
                                @elseif($customer->loginType==3)
                                    Google
                                @elseif($customer->loginType==4)
                                    Apple
                                @else
                                @endif                    
                            </div>
                        @endif <!-- socialMediaType -->
                        @if(isset($customer) && $customer->socialId!='')
                            <div class="form-group">
                                <label>socialMediaId :</label>
                                {{$customer->socialId}}           
                            </div>
                        @endif 
                        <button class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop  


<!--  <div class="form-group" {{ $errors->has('lastname') ? ' has-error' : '' }}>
   <label>Last Name <span class="req">*</span></label>
   <input type="text" maxlength="50" class="form-control"  value="@if(isset($customer)){{$customer->lastname}}@endif" name="lastname"
   placeholder="Last Name">
   @if ($errors->has('lastname'))
   <span class="help-block">
       {{ $errors->first('lastname') }}
   </span>
   @endif
   </div> -->


<!-- <div class="form-group">
   <label>Gender <span class="req">*</span></label>
   <br>
   <input type="radio"  class="" value="1" name="gender" @if(isset($customer) && $customer->gender==1) checked @endif><span style="margin-left:5px;">Male</span>
   <input type="radio"  class="" value="2" name="gender" @if(isset($customer) && $customer->gender==2) checked @endif><span style="margin-left:5px;">Female</span>
   </div>
   <div class="form-group">
   <label>Birth Date </label>
   <input type="text" maxlength="20" class="form-control ls-datepicker" value="@if(isset($customer) && $customer->birthDate !='' && $customer->birthDate != '0000-00-00' && $customer->birthDate != '1970-01-01'){{date('m/d/Y',strtotime($customer->birthDate))}}@endif" name="birthDate" placeholder="Birth Date">
   </div> -->

<!-- <div class="form-group">
   <label>Country of Residence</label>
   <select name="country" id="country"  class="form-control ls-select2">
       <option value="0">Select Country</option>
       @if ($country)
       @foreach($country as $con)
       <option value="{{$con->id}}" @if(isset($customer) && $customer->countryId == $con->id) selected="selected" @endif>{{$con->name}}</option>
       @endforeach
       @endif
   </select>
   </div> -->
            
          
                              
            