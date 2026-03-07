@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/emailtemplate/validation.js"></script>
    <script src="/assets/admin/js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="/assets/admin/js/ckfinder/ckfinder.js"></script>
    <script type="text/javascript">
                         var editor = CKEDITOR.replace( 'templateDescription', {
    //CKEDITOR.config.removeDialogTabs = 'image:advanced;link:advanced;flash:advanced;creatediv:advanced;editdiv:advanced';                      
    'filebrowserImageBrowseUrl': '/assets/admin/js/ckeditor/plugins/imgbrowse/imgbrowse.html',
    'filebrowserImageUploadUrl': '/assets/admin/js/ckeditor/plugins/imgupload.php',

});
CKFinder.setupCKEditor( editor, '../' );
                         </script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($emailtemplate)) Edit @else Add @endif Email Template</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/emailtemplate/')}}">Email Template</a></li>
                <li class="breadcrumb-item active">@if(isset($emailtemplate)) Edit @else Add @endif Email Template</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($emailtemplate)){{ URL::to('admin/emailtemplate/edit/'.$emailtemplate->templateId.'')}}@else{{ URL::to('admin/emailtemplate/add') }}@endif" name="emailtemplate" novalidate>
                    {{csrf_field()}}
					
					@if(isset($emailtemplate))
		            <input type="hidden" name="edit_id" value="{{$emailtemplate->templateId}}"> 
	                @endif
					
					<div class="form-group">
                        <label>Email Template Name <span class="req">*</span></label>
                        <input type="text" maxlength="50" class="form-control" value="@if(isset($emailtemplate)){{$emailtemplate->templateName}}@endif" name="templateName"
                               placeholder="Email Template Name">
                    </div>
                   <div class="form-group">
                        <label id="exampleFormControlTextarea1">Email Template Description</label>
                        <textarea id="exampleFormControlTextarea1" placeholder="Description" class="form-control" id="templateDescription" name="templateDescription">@if(isset($emailtemplate)){{$emailtemplate->templateDescription}}@endif</textarea>
                        
                    </div>
					<div class="form-group">
                        <label>Email Template Type <span class="req">*</span></label>
                        <select name="type" class="form-control ls-select2">
                            <option value="">Select Email Template Type</option>
                            <option value="1" @if(isset($emailtemplate) && $emailtemplate->type==1) selected @endif>Vendor Registration</option>
                            <option value="2" @if(isset($emailtemplate) && $emailtemplate->type==2) selected @endif>Vendor Verified</option>
                            
                        </select>
                    </div>
					<div class="form-group">
                        <label>Status</label>
						<select name="templateStatus" class="form-control ls-select2">
							<option value="1" @if(isset($emailtemplate) && $emailtemplate->templateStatus==1) selected @endif>Active</option>
							<option value="0" @if(isset($emailtemplate) && $emailtemplate->templateStatus==0) selected @endif>Inactive</option>
							
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
