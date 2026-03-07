@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/levelmanagement/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($lessionmanagement)) Edit @else Add @endif Lesson</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/lessionmanagement/')}}">Lesson Management</a></li>
                <li class="breadcrumb-item active">@if(isset($lessionmanagement)) Edit @else Add @endif Lesson</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($lessionmanagement)){{ URL::to('admin/lessionmanagement/edit/'.$lessionmanagement->lessionId.'')}}@else{{ URL::to('admin/lessionmanagement/add') }}@endif" name="lessionmanagement" novalidate>
                    {{csrf_field()}}
					
					@if(isset($lessionmanagement))
		            <input type="hidden" name="edit_id" value="{{$lessionmanagement->lessionId}}"> 
	                @endif
					
                    
					<div class="form-group">
                        <label>Lesson Name<span class="req">*</span></label>
                        <input type="text" maxlength="50" required="required" class="form-control" value="@if(isset($lessionmanagement)){{$lessionmanagement->lessionName}}@endif" name="lessionName" placeholder="Basic">
                    </div>

                    <div class="form-group">
                        <label>Lesson Description<span class="req">*</span></label>
                        <textarea name="lessionDescription" id="lessionDescription" required="required" maxlength="300" class="form-control" rows="5" cols="5">@if(isset($lessionmanagement)){{$lessionmanagement->lessionDescription}}@endif</textarea>
                        
                    </div>

                    <div class="form-group">
                        <label>Display Rank</label>
                         <input type="text" name="sortOrder" style="width:20%;" maxlength="5" tabindex="3"  id="sortOrder" class="form-control min_width1" placeholder="Rank" value="@if(isset($lessionmanagement)){{$lessionmanagement->sortOrder}}@else{{$rank}}@endif{{ old('rank') }}">
                     </div>

					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2">
							<option value="1" @if(isset($lessionmanagement) && $lessionmanagement->isActive==1) selected @endif>Active</option>
							<option value="0" @if(isset($lessionmanagement) && $lessionmanagement->isActive==0) selected @endif>Inactive</option>
							
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
