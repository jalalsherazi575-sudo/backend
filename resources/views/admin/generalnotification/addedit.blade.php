@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/generalnotification/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">Send General Notification</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                
                <li class="breadcrumb-item active">Send General Notification</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('admin/generalnotification/add') }}" name="generalnotification" novalidate>
                    {{csrf_field()}}
					
					
					
                    <div class="form-group">
                        <label>Select Device</label>
                        <select name="select_device" class="form-control ls-select2">
                            <option value="0">Select All</option>
                            <option value="1">Android</option>
                            <option value="2">Iphone</option>
                            
                        </select>
                    </div>

                    

					<div class="form-group">
                        <label>Message<span class="req">*</span></label>
                        
                         <textarea name="message" maxlength="160" cols="5" rows="5" class="form-control"></textarea>
                         <span><strong>Note :-</strong> max character could be enter 160 character</span>      
                    </div>
                    
					
					
					
					
					
                    
                   
                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
