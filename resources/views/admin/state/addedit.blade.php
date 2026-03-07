@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/productcategory/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($state)) Edit @else Add @endif State</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/state/')}}">State</a></li>
                <li class="breadcrumb-item active">@if(isset($state)) Edit @else Add @endif State</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($state)){{ URL::to('admin/state/edit/'.$state->id.'')}}@else{{ URL::to('admin/state/add') }}@endif" name="state" novalidate>
                    {{csrf_field()}}
					
					@if(isset($state))
		            <input type="hidden" name="edit_id" value="{{$state->id}}"> 
	                @endif
					
                    <div class="form-group">
                        <label>Country<span class="req">*</span></label>
                        <select name="country_id" required="required" class="form-control ls-select2">
                            <option value="">Select Country</option>
                            @if($country)
                                @foreach($country as $vals)
                            <option value="{{$vals->id}}" @if(isset($state) && $state->country_id==$vals->id) selected @endif>{{$vals->name}}</option>
                            
                                @endforeach
                             @endif 
                        </select>
                    </div>

                    @if ($language)
                      @foreach ($language as $value)
					<div class="form-group">
                        <label>State Name ({{$value->title}})<span class="req">*</span></label>
                        <input type="text" maxlength="50" class="form-control" required="required" value="@if(isset($state)){{Laraspace\Http\Controllers\CommanController::getStateValue($state->id,$value->id) }}@endif" name="name[{{$value->id}}]"
                               placeholder="State Name">
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
