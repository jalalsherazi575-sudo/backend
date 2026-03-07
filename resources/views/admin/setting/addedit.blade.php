@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/setting/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($setting)) Edit @else Add @endif Setting</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/setting/')}}">Setting</a></li>
                <li class="breadcrumb-item active">@if(isset($setting)) Edit @else Add @endif Setting</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($setting)){{ URL::to('admin/setting/edit/'.$setting->id.'')}}@else{{ URL::to('admin/setting/add') }}@endif" name="setting" novalidate>
                    {{csrf_field()}}
					
					@if(isset($setting))
		            <input type="hidden" name="edit_id" value="{{$setting->id}}"> 
	                @endif
					
					<div class="form-group">
                        <label>Title Key <span class="req">*</span></label>
                        <input type="text" maxlength="50" @if(isset($setting)) readonly @endif class="form-control" value="@if(isset($setting)){{$setting->option}}@endif" name="option"
                               placeholder="Title Key">
                    </div>
                    
					
					<div class="form-group">
                        <label>Title Value <span class="req">*</span></label>
                        <input type="text" maxlength="50"  class="form-control" value="@if(isset($setting)){{$setting->value}}@endif" name="value"
                               placeholder="Title Value">
                    </div>
					
					
					
                    
                   
                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
