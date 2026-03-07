@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/version/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">Edit  Version</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/version/')}}">Version</a></li>
                <li class="breadcrumb-item active">Edit Version</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($version)){{ URL::to('admin/version/edit/'.$version->id.'')}}@else{{ URL::to('admin/version/add') }}@endif" name="version" novalidate>
                    {{csrf_field()}}
					
					@if(isset($version))
		            <input type="hidden" name="edit_id" value="{{$version->id}}">
				    @endif
					
					<div class="form-group">
                        <label>Type </label>
                         @if(isset($version))
						  @if(isset($version) && $version->app_type==1)
						  Android
					      @else
						  Iphone
					      @endif
					    @endif
					</div>
					<div class="form-group">
                        <label>Version <span class="req">*</span></label>
                        <input type="text" class="form-control" name="app_version" placeholder="App Version" value="@if(isset($version)){{$version->app_version}}@endif">
                    </div>
                    <div class="form-group">
                        <label>URL <span class="req">*</span></label>
                        <input type="text" class="form-control" name="url" placeholder="Url" value="@if(isset($version)){{$version->app_url}}@endif">
                    </div>
					<!--<div class="form-group">
                        <label>Culture Code</label>
                        <input type="text" class="form-control" name="culture_code" placeholder="Culture Code" value="@if(isset($version)){{$version->culture_code}}@endif">
                    </div>-->
					<div class="form-group">
                        <label>Is update available <span class="req">*</span></label>
						<select name="is_update_available" class="form-control ls-select2">
							<option value="0" @if(isset($version) && $version->is_update_available==0) selected @endif>No update available</option>
							<option value="1" @if(isset($version) && $version->is_update_available==1) selected @endif>No mandatory update available</option>
							<option value="2" @if(isset($version) && $version->is_update_available==2) selected @endif>Mandatory update available</option>
							<option value="3" @if(isset($version) && $version->is_update_available==3) selected @endif>App is under maintenance</option>
						</select>
                    </div>
					<div class="form-group">
                        <label>Is App Approved <span class="req">*</span></label>
						<select name="is_approved" class="form-control ls-select2">
							<option value="1" @if(isset($version) && $version->is_approved==1) selected @endif>Active</option>
							<option value="0" @if(isset($version) && $version->is_approved==0) selected @endif>Inactive</option>
							
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
