@extends('admin.layouts.layout-basic')

@section('scripts')
  <style type="text/css">
    #country,.select2-container {min-width: 300px; width:100% !important;}
  </style>
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
  maxDate: '-16Y',
  //endDate: "today",
  //maxDate: today,
  yearRange: "-116:+0"
 }
);
});

$("#role").change(function(){

var role_name=$('#role option:selected').html()
  //alert(role_name);
  if (role_name=='Consumer Manager') {
        $('#countryId').show();
        $("#country").prop('required',true);
  } else {
        $('#countryId').hide();
        $("#country").prop('required',false);
  }

});

</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($users)) Edit @else Add @endif Admin User</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/users/')}}">Admin Users</a></li>
                <li class="breadcrumb-item active">@if(isset($users)) Edit @else Add @endif Admin User</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($users)){{ URL::to('admin/users/edit/'.$users->id.'')}}@else{{ URL::to('admin/users/create') }}@endif" name="user" novalidate>
                    {{csrf_field()}}
					
					@if(isset($users))
		            <input type="hidden" name="edit_id" value="{{$users->id}}"> 
	                @endif
					
					           <div class="form-group">
                        <label>Admin Name <span class="req">*</span></label>
                        <input type="text" maxlength="100" class="form-control" value="@if(isset($users)){{$users->name}}@endif" name="name"
                               placeholder="Name">
                    </div>
					
					           <div class="form-group">
                        <label>Admin Email <span class="req">*</span></label>
                        <input type="email" maxlength="60" class="form-control" value="@if(isset($users)){{$users->email}}@endif" name="email"placeholder="Admin Email">
                    </div>
					          <div class="form-group">
                        <label>Admin Password </label>
                        <input type="password" maxlength="25" class="form-control" value="" name="password"
                               placeholder="Password">
                    </div>

                    <div class="form-group">
                        <label>Select Role</label>
                        <select name="role" id="role" required="required"  class="form-control ls-select2">
                          <option value="">Select Role</option>
                                           @if ($userroles)
                                               @foreach($userroles as $roles)
                                            <option value="{{$roles->role_id}}" @if(isset($users) && $users->role_id==$roles->role_id) selected="selected" @endif>{{$roles->role_name}}</option>
                                               @endforeach
                                             @endif    
                        </select>
                        <input type="hidden" name="isCountry" value="isCountry" value="0">
                        
                    </div>

                    <div class="form-group" id="countryId" @if(isset($users) && $users->countryId!=0 && $users->countryId!='') @else style="display:none;" @endif>
                        <label>Country</label>
                        <br>
                        <select name="countryId" id="country"   class="form-control ls-select2">
                          <option value="">Select Country</option>
                                @if ($country)
                                     @foreach($country as $con)
                                  <option value="{{$con->id}}" @if(isset($users) && $users->countryId==$con->id) selected="selected" @endif>{{$con->name}}</option>
                                     @endforeach
                                   @endif         
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
