@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/subscriptionplan/validation.js"></script>
    <script src="/assets/admin/js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="/assets/admin/js/ckfinder/ckfinder.js"></script>
    <script type="text/javascript">
        var editor= CKEDITOR.replace( 'description', {
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
            <h3 class="page-title">@if(isset($cmspages)) Edit @else Add @endif Cms Page</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/cmspages/')}}">Cms Page</a></li>
                <li class="breadcrumb-item active">@if(isset($cmspages)) Edit @else Add @endif Cms Page</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="cmsform" enctype="multipart/form-data" method="post" action="@if(isset($cmspages)){{ URL::to('admin/cmspages/edit/'.$cmspages->id.'')}}@else{{ URL::to('admin/cmspages/add') }}@endif" name="cmspages" novalidate>
                    {{csrf_field()}}
					
					@if(isset($cmspages))
		            <input type="hidden" name="edit_id" value="{{$cmspages->id}}"> 
	                @endif
                    <div class="form-group">
                        <label>Name<span class="req">*</span></label>
                        <input type="text" maxlength="70" class="form-control @error('name') is-invalid @enderror" value="{{ isset($cmspages) ? old('name', $cmspages->name) : old('name') }}" name="name" placeholder="Page Name">
                        @if ($errors->has('name'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('name') }}
                                </span>
                        @endif
                    </div>
                      
                    
                     
                    <div class="form-group">
                        <label>Descripion</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" cols="10" rows="10" placeholder="Descripion">{{ isset($cmspages) ? old('name', $cmspages->description ) : old('description ') }}</textarea>
                        @if ($errors->has('description'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('description') }}
                                </span>
                        @endif
                    </div>
                      
					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2">
							<option value="1" @if(isset($cmspages) && $cmspages->isActive==1) selected @endif>Active</option>
							<option value="0" @if(isset($cmspages) && $cmspages->isActive==0) selected @endif>Inactive</option>
							
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
