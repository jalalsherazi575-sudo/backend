@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/languages/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($languages)) Edit @else Add @endif Language</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/languages/')}}">Language</a></li>
                <li class="breadcrumb-item active">@if(isset($languages)) Edit @else Add @endif Language</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($languages)){{ URL::to('admin/languages/edit/'.$languages->id.'')}}@else{{ URL::to('admin/languages/add') }}@endif" name="languages" novalidate>
                    {{csrf_field()}}
					
					@if(isset($languages))
		            <input type="hidden" name="edit_id" value="{{$languages->id}}"> 
	                @endif
					
					<div class="form-group">
                        <label>Language Name <span class="req">*</span></label>
                        <input type="text" maxlength="50" class="form-control" value="@if(isset($languages)){{$languages->title}}@endif" name="title"
                               placeholder="English">
                    </div>
                    <div class="form-group">
                        <label>Language Code</label>
                        <input type="text" maxlength="50"  class="form-control" value="@if(isset($languages)){{$languages->lancode}}@endif" name="lancode"
                               placeholder="En">
                    </div>
                    <div class="form-group">
                        <label>Order<span class="req">*</span></label>
                        <input type="text"  maxlength="10" style="width:50%;" class="form-control" value="@if(isset($languages)){{$languages->sort_order}}@elseif(isset($order)){{$order}}@endif" name="sort_order"
                               placeholder="">
                    </div>
                    <div class="form-group">
                        <label>Language Direction <span class="req">*</span></label>
                         <select name="landir" class="form-control ls-select2">
                            <option value="ltr" @if(isset($languages) && $languages->landir=='ltr') selected @endif>ltr</option>
                            <option value="rtl" @if(isset($languages) && $languages->landir=='rtl') selected @endif>rtl</option>
                        </select>
                        
                    </div>
                    <div class="form-group">
                        <label>Is Default</label>
                        <select name="is_default" class="form-control ls-select2">
                            <option value="No" @if(isset($languages) && $languages->is_default=='No') selected @endif>No</option>
                            <option value="Yes" @if(isset($languages) && $languages->is_default=='Yes') selected @endif>Yes</option>
                            
                            
                        </select>
                    </div>
					
					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2">
							<option value="Active" @if(isset($languages) && $languages->status=='Active') selected @endif>Active</option>
							<option value="Inactive" @if(isset($languages) && $languages->status=='Inactive') selected @endif>Inactive</option>
							
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
