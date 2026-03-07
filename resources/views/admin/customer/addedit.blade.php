@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/customer/validation.js"></script>
	<script>
$(function() {
//var dateToday = new Date();
var today = new Date();
$( ".ls-datepicker" ).datepicker(
 {
  changeMonth: true,
  //dateFormat: 'yy-mm-dd',
  dateFormat: 'dd.mm.yy',
  //yearRange: '1950:2006',
  changeYear: true,
  //minDate: 0, 
 // maxDate: '-16Y',
  //endDate: "today",
  maxDate: '-1D',
  yearRange: "-116:+0"
 }
);

 $('#proofTypeId').change(function(){
    var proofTypeId = $(this).val();
     if (proofTypeId!=0) {
       $('#proofdoc').show();
     } else {
       $('#proofdoc').hide();
     }

  });

});

function removeIDProof(proofId) {
    

    if (proofId!='' && confirm("Are you sure want to delete this id proof?")) {
  $.ajax({
        url: "{{URL::to('admin/')}}/customer/deleteIdProof/"+proofId,
        type: "get",
        
    success: function(html){
          if (html!='') {
            
            alert("You have successfully deleted Id Proof File.");
            window.location.reload();
            }
        }
        });
  }
}

</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($customer)) Edit @else Add @endif Customer</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/customer/')}}">Customer</a></li>
                <li class="breadcrumb-item active">@if(isset($customer)) Edit @else Add @endif Customer</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($customer)){{ URL::to('admin/customer/edit/'.$customer['id'].'')}}@else{{ URL::to('admin/customer/add') }}@endif" name="customer" novalidate>
                    {{csrf_field()}}
					
    					       @if(isset($customer))
    		            <input type="hidden" name="edit_id" value="{{$customer['id']}}"> 
    	                @endif
					
					          <div class="form-group">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" maxlength="40" class="form-control" value="@if(isset($customer)){{$customer['fname']}}@endif" name="fname" placeholder="First Name">
                    </div>
					          
                    <div class="form-group">
                        <label>Last Name </label>
                        <input type="text" maxlength="40" class="form-control" value="@if(isset($customer)){{$customer['lname']}}@endif" name="lname" placeholder="Last Name">
                    </div>

					          <div class="form-group">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" maxlength="50" class="form-control" value="@if(isset($customer)){{$customer['email']}}@endif" name="email" placeholder="Email">
                    </div>

					          <div class="form-group">
                        <label>Password </label>
                        <input type="password" maxlength="20" class="form-control" value="" name="password" placeholder="Password">
                    </div>

                    <div class="form-group">
                        <label>Gender <span class="req">*</span></label>
                        <br>
                        <input type="radio"  class="" value="1" name="gender" @if(isset($customer) && $customer['gender']==1) checked @endif><span style="margin-left:5px;">Male</span>
                        <input type="radio"  class="" value="2" name="gender" @if(isset($customer) && $customer['gender']==2) checked @endif><span style="margin-left:5px;">Female</span> 
                    </div>

                    <div class="form-group">
                        <label>Birth Date </label>
                        <input type="text" maxlength="20" class="form-control ls-datepicker" value="@if(isset($customer) && $customer['birthDate']!='' && $customer['birthDate']!='0000-00-00' && $customer['birthDate']!='1970-01-01'){{date('m/d/Y',strtotime($customer['birthDate']))}}@endif" name="birthdate" placeholder="Birth Date">
                    </div>

                     <div class="form-group">
                        <label>Phone</label>
                        <input type="text" maxlength="13" class="form-control" value="@if(isset($customer)){{$customer['phone']}}@endif" name="phone" placeholder="Phone">
                     </div>
                     
                     <div class="form-group">
                        <label>Profile Picture </label>
                        <input type="file" class="form-control-file" name="photo" placeholder="Photo">
                         @if(isset($customer) && $customer['photo']!='')
                         <img src="{{$customer['photo']}}" width="100px">
                           @endif
                     </div>

                    <div class="form-group">
                        <label>Country of Residence</label>
                        <select name="countryId" id="country"  class="form-control ls-select2">
                          <option value="0">Select Country</option>
                                @if ($country)
                                     @foreach($country as $con)
                                  <option value="{{$con->id}}" @if(isset($customer) && $customer['countryId']==$con->id) selected="selected" @endif>{{$con->name}}</option>
                                     @endforeach
                                   @endif         
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Area of Interest<span class="req">*</span></label>
                          <select name="interestId[]" data-placeholder="Area of Interest" multiple="multiple" class="form-control ls-select2">
                            <option value="">Select Area of Interest</option>
                            @if($areaofinterest)
                              @foreach($areaofinterest as $vals)
                            <option value="{{$vals->id}}" @if(isset($customer) && isset($customerAreaofInterestList) && in_array($vals->id,$customerAreaofInterestList)) selected @endif>{{$vals->name}}</option>
                                @endforeach
                             @endif 
                          </select>
                    </div>

                    <div class="form-group">
                        <label>Photo ID Proof</label>
                        <select name="proofTypeId" id="proofTypeId"  class="form-control ls-select2">
                          <option value="0">Select Photo ID Proof</option>
                                @if ($idprooftype)
                                     @foreach($idprooftype as $con)
                                  <option value="{{$con->id}}" @if(isset($customer) && $customer['proofTypeId']==$con->id) selected="selected" @endif>{{$con->name}}</option>
                                     @endforeach
                                   @endif         
                        </select>
                    </div>

                    <div class="form-group" @if(isset($customer) && $customer['proofTypeId']!=0) @else style="display:none;" @endif id="proofdoc">
                        <label>Photo ID Proof Doc </label>
                        <input type="file" class="form-control-file" name="proofType[]" multiple="multiple" placeholder="Photo">

                    </div>

                    
                     @if(isset($customerProofIdList))
                     <div class="row">
                          @foreach($customerProofIdList as  $value)
                           
                          <div style="margin-left:25px;margin-bottom:10px;"><img src="{{$value['proofPhoto']}}" width="100px" height="100px" style="margin-bottom:10px;"><br><input type="button" name="removeProof" id="remove" value="Remove Proof" onclick="removeIDProof({{$value['id']}});" class="btn btn-primary" ></div>
                          @endforeach
                     </div>
                     @endif

                     <div class="form-group">
                        <label>Is Profile Verified</label>
                        <select name="isVerify" id="isVerify"  class="form-control ls-select2">
                          <option value="">Select Profile Verified</option>
                          <option value="1" @if(isset($customer) && $customer['isVerify']==1) selected @endif>Yes</option>
                          <option value="0" @if(isset($customer) && $customer['isVerify']==0) selected @endif>No</option>      
                        </select>
                    </div>
                     

					
					         
					
					@if(isset($customer))
					<div class="form-group">
                        <label>Login Status :</label>
						<br>
                        @if($customer['loginStatus']==1)
                          Yes
                        @else
                          No
                        @endif					  
                    </div>
				    @endif
                     
                    @if(isset($customer))
					<div class="form-group">
                        <label>Last Login Date :</label>
						<br>
                        @if($customer['lastLoginDate']!='')
						{{$customer['lastLoginDate']}}
                        @endif					  
                    </div>
				    @endif	

                    @if(isset($customer))
					<div class="form-group">
                        <label>Created Date :</label>
                        <br>
						@if($customer['createdDate']!='')
						{{$customer['createdDate']}}
                        @endif					  
                    </div>
				    @endif

                    @if(isset($customer))
					<div class="form-group">
                        <label>Device Type :</label>
                        <br>
						@if($customer['deviceType']==1)
						Android
					    @elseif($customer['deviceType']==2)
						Iphone
						@else
                        @endif					  
                    </div>
				    @endif

            @if(isset($customer))
          <div class="form-group">
                        <label>Device Details :</label>
                        <br>
            @if($customer['deviceDetails']!='')
            {{$customer['deviceDetails']}}
                        @endif            
                    </div>
            @endif

                     @if(isset($customer) && $customer['loginType']!=1)
					<div class="form-group">
                        <label>socialMediaType :</label>
                        <br>
						@if($customer['loginType']==1)
						App
					    @elseif($customer['loginType']==2)
						Facebook
						@elseif($customer['loginType']==3)
						Google
						@elseif($customer['loginType']==4)
						Twitter
						@else
                        @endif					  
                    </div>
				    @endif					

            @if(isset($customer) && $customer['socialMediaId']!='')
          <div class="form-group">
                        <label>socialMediaId :</label>
                        <br>
                         {{$customer['socialMediaId']}}           
                    </div>
            @endif          
					
					@if(isset($customer))
					<div class="form-group">
                        <label>Device Token :</label>
                        <br>
						@if($customer['deviceToken']!='')
						{{$customer['deviceToken']}}
                        @endif					  
                    </div>
				    @endif
					
					<div class="form-group">
                        <label>Status</label>
						<select name="isActive" class="form-control ls-select2">
							<option value="1" @if(isset($customer) && $customer['isActive']==1) selected @endif>Active</option>
							<option value="0" @if(isset($customer) && $customer['isActive']==0) selected @endif>Inactive</option>
							
						</select>
                    </div>
                    
                   
                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
