@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/productcategory/validation.js"></script>
    <script type="text/javascript">
       @php if(!empty($city) && $city->country_id != null){ @endphp
        $( document ).ready(function() {   
          
        var countryID = @php echo $city->country_id; @endphp ;
        var stateID = @php echo $city->state_id; @endphp ;
    

          if(countryID){
        $.ajax({
           type:"GET",
           url:"{{url('comman/get-state-list')}}?country_id="+countryID,
           success:function(res){               
            if(res){
                $("#state").empty();
                $("#state").append('<option value="">Select</option>');
                
                $.each(res,function(key,value){
                    if(stateID == key){ 
                        selected='selected="selected"';
                    }else{
                        selected="";
                    }
                    $("#state").append('<option '+selected+' value="'+key+'">'+value+'</option>');
                });
           
            }else{
               $("#state").empty();
            }
           }
        });
    }else{
        $("#state").empty();
        //$("#city").empty();
    }
        });

@php } @endphp

        $('#country').change(function(){
    var countryID = $(this).val();    
    if(countryID){
        $.ajax({
           type:"GET",
           url:"{{url('comman/get-state-list')}}?country_id="+countryID,
           success:function(res){               
            if(res){
                $("#state").empty();
                $("#state").append('<option>Select State</option>');
                $.each(res,function(key,value){
                    $("#state").append('<option value="'+key+'">'+value+'</option>');
                });
           
            }else{
               $("#state").empty();
            }
           }
        });
    }else{
        $("#state").empty();
       // $("#city").empty();
    }      
   });
    </script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center12">
            <h3 class="page-title">@if(isset($city)) Edit @else Add @endif City</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/city/')}}">City</a></li>
                <li class="breadcrumb-item active">@if(isset($city)) Edit @else Add @endif City</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($city)){{ URL::to('admin/city/edit/'.$city->id.'')}}@else{{ URL::to('admin/city/add') }}@endif" name="city" novalidate>
                    {{csrf_field()}}
					
					@if(isset($city))
		            <input type="hidden" name="edit_id" value="{{$city->id}}"> 
	                @endif
					
                    <div class="form-group">
                        <label>Country<span class="req">*</span></label>
                        <select name="country_id" id="country" required="required" class="form-control ls-select2">
                            <option value="">Select Country</option>
                            @if($country)
                                @foreach($country as $vals)
                            <option value="{{$vals->id}}" @if(isset($city) && $city->country_id==$vals->id) selected @endif>{{$vals->name}}</option>
                            
                                @endforeach
                             @endif 
                        </select>
                    </div>
                    
                    <div class="form-group">
                            <label>State<span class="req">*</span></label>
                            <select id="state" name="state_id" class="form-control ls-select2" required="required">
                            <option value="">Select State</option>
                            </select>
                    </div>

                    @if ($language)
                      @foreach ($language as $value)
					<div class="form-group">
                        <label>City Name ({{$value->title}})<span class="req">*</span></label>
                        <input type="text" maxlength="50" required="required" class="form-control"  value="@if(isset($city)){{Laraspace\Http\Controllers\CommanController::getCityValue($city->id,$value->id) }}@endif" name="name[{{$value->id}}]"
                               placeholder="City Name">
                    </div>
                       @endforeach
                    @endif
                    
					

					
                    
                   
                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
