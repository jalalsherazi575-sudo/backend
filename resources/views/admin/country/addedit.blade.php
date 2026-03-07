@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/productcategory/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($country)) Edit @else Add @endif Country</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/country/')}}">Country</a></li>
                <li class="breadcrumb-item active">@if(isset($country)) Edit @else Add @endif Country</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($country)){{ URL::to('admin/country/edit/'.$country->id.'')}}@else{{ URL::to('admin/country/add') }}@endif" name="country" novalidate>
                    {{csrf_field()}}
					
					@if(isset($country))
		            <input type="hidden" name="edit_id" value="{{$country->id}}"> 
	                @endif
					
                    @if ($language)
                      @foreach ($language as $value)
					<div class="form-group">
                        <label>Country Name ({{$value->title}})<span class="req">*</span></label>
                        <input type="text" maxlength="50" class="form-control" required="required" value="@if(isset($country)){{Laraspace\Http\Controllers\CommanController::getCountryValue($country->id,$value->id) }}@endif" name="name[{{$value->id}}]"
                               placeholder="Country Name">
                    </div>
                       @endforeach
                    @endif
                    
					<div class="form-group">
                        <label>Currency Name </label>
                        <input type="text" maxlength="50" class="form-control"  value="@if(isset($country)){{$country->currency}}@endif" name="currency"
                               placeholder="Currency Name">
                    </div>

                    <div class="form-group">
                        <label>Country Iso Code </label>
                        <input type="text" maxlength="50" class="form-control"  value="@if(isset($country)){{$country->iso2}}@endif" name="iso2" placeholder="Iso Code">
                    </div>

                    <div class="form-group">
                        <label>Country Currency Symbol</label>
                        <input type="text" maxlength="50" class="form-control"  value="@if(isset($country)){{$country->symbol}}@endif" name="symbol" placeholder="$">
                    </div>

					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2">
							<option value="1" @if(isset($country) && $country->status==1) selected @endif>Active</option>
							<option value="0" @if(isset($country) && $country->status==0) selected @endif>Inactive</option>
							
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
