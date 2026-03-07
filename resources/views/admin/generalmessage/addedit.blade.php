@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/message/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($message)) Edit @else Add @endif Message</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/generalmessage/')}}">Message</a></li>
                <li class="breadcrumb-item active">@if(isset($message)) Edit @else Add @endif Message</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($message)){{ URL::to('admin/generalmessage/edit/'.$message->id.'')}}@else{{ URL::to('admin/generalmessage/add') }}@endif" name="bank" novalidate>
                    {{csrf_field()}}
					
					@if(isset($message))
		            <input type="hidden" name="edit_id" value="{{$message->id}}"> 
	                @endif
					
					<div class="form-group">
                        <label>Title Key <span class="req">*</span></label>
                        <input type="text" maxlength="50" @if(isset($message)) readonly @endif class="form-control" value="@if(isset($message)){{$message->title_key}}@endif" name="title_key"
                               placeholder="Title">
                    </div>
                    
					@if ($language)
					  @foreach ($language as $value)
				    <div class="form-group">
                        <label>Message<span class="req">*</span></label>
                        <input type="text" maxlength="250" required class="form-control" value="@if(isset($message)){{ Laraspace\Http\Controllers\CommanController::getMessageValue($message->id,$value->id) }}@endif" name="general_message[{{$value->id}}]"
                               placeholder="Message">
                    </div>
					  @endforeach
					@endif
					
					
					
					<div class="form-group">
                        <label>Is App Message</label>
						<select name="is_app_msg" class="form-control ls-select2">
							<option value="0" @if(isset($message) && $message->is_app_msg==0) selected @endif>No</option>
							<option value="1" @if(isset($message) && $message->is_app_msg==1) selected @endif>Yes</option>
							
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
