@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/message/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($notificationmessage)) Edit @else Add @endif Notification Message</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/notificationmessage/')}}">Notification Message</a></li>
                <li class="breadcrumb-item active">@if(isset($notificationmessage)) Edit @else Add @endif Notification Message</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($notificationmessage)){{ URL::to('admin/notificationmessage/edit/'.$notificationmessage->id.'')}}@else{{ URL::to('admin/notificationmessage/add') }}@endif" name="notificationmessage" novalidate>
                    {{csrf_field()}}
					
					@if(isset($notificationmessage))
		            <input type="hidden" name="edit_id" value="{{$notificationmessage->id}}"> 
	                @endif
					
					<div class="form-group">
                        <label>Title Key <span class="req">*</span></label>
                        <input type="text" maxlength="50" @if(isset($notificationmessage)) readonly @endif class="form-control" value="@if(isset($notificationmessage)){{$notificationmessage->title_key}}@endif" name="title_key"
                               placeholder="Title">
                    </div>
                    
					@if ($language)
					  @foreach ($language as $value)
				    <div class="form-group">
                        <label>Message({{$value->title}})<span class="req">*</span></label>
                        <input type="text" maxlength="150" required class="form-control" value="@if(isset($notificationmessage)){{ Laraspace\Http\Controllers\CommanController::getNotificationMessageValue($notificationmessage->id,$value->id) }}@endif" name="general_message[{{$value->id}}]"
                               placeholder="{{$value->title}} Message">
                    </div>
					  @endforeach
					@endif
					
					
					
					<div class="form-group">
                        <label>Status</label>
						<select name="isActive" class="form-control ls-select2">
                            <option value="1" @if(isset($notificationmessage) && $notificationmessage->isActive==1) selected @endif>Active</option>
							<option value="0" @if(isset($notificationmessage) && $notificationmessage->isActive==0) selected @endif>Inactive</option>
							
							
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
