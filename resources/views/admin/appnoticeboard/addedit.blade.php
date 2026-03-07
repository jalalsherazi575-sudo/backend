@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/appnoticeboard/validation.js"></script>

    <script type="text/javascript">

    function removeImage(Id) {
    

        if (Id!='' && confirm("Are you sure want to delete this picture?")) {

             $.ajax({

                    url: "{{URL::to('admin/')}}/appnoticeboard/deleteImages/"+Id,
                    type: "get",
                    
                    success: function(html) {

                      if (html!='') {
                        
                        alert("You have successfully deleted Picture.");
                        window.location.reload();

                        }
                    }

                });

       }

    }

   </script>  

@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($appnoticeboard)) Edit @else Add @endif Boarding Screen</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/appnoticeboard/')}}">Boarding Screen</a></li>
                <li class="breadcrumb-item active">@if(isset($appnoticeboard)) Edit @else Add @endif Boarding Screen</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($appnoticeboard)){{ URL::to('admin/appnoticeboard/edit/'.$appnoticeboard->id.'')}}@else{{ URL::to('admin/appnoticeboard/add') }}@endif" name="appnoticeboard" novalidate>
                    {{csrf_field()}}
					
					        @if(isset($appnoticeboard))
		            <input type="hidden" name="edit_id" value="{{$appnoticeboard->id}}"> 
	                @endif
					
                    <div class="form-group">
                        <label>Description<span class="req">*</span></label>
                        <textarea name="description" id="description" required="required" maxlength="300" class="form-control" rows="5" cols="5">@if(isset($appnoticeboard)){{$appnoticeboard->description}}@endif</textarea>
                        
                    </div>

                    <div class="form-group" id="vocals">
                         <label>Upload Photo</label>
                            <input type="file" style="width:60%;" name="photo"  id="photo" class="form-control" placeholder="Photo">
                            
                            @if (isset($appnoticeboard) && $appnoticeboard->photo!='')
                             
                             <div class="row">
                                  <div style="margin-left:25px;margin-bottom:10px;margin-top:10px;"> 

                                    <img src="{{ URL::to('/')}}/appnoticeboard/{{$appnoticeboard->photo}}" width="100px" height="100px" style="margin-bottom:10px;">
                                    
                                    <br><input type="button" name="removeProof" style="margin-top:5px;" id="remove" value="Remove Photo" onclick="removeImage({{$appnoticeboard->id}});" class="btn btn-primary" >
                                  </div>
                                  
                             </div>

                             @endif
                    </div>  

                    <div class="form-group">
                        <label>Display Rank</label>
                         <input type="text" name="sortOrder" style="width:20%;" maxlength="5" tabindex="3"  id="sortOrder" class="form-control min_width1" placeholder="Rank" value="@if(isset($appnoticeboard)){{$appnoticeboard->sortOrder}}@else{{$rank}}@endif{{ old('rank') }}">
                     </div>

					          <div class="form-group">
                        <label>Status</label>
            						<select name="status" class="form-control ls-select2">
            							<option value="1" @if(isset($appnoticeboard) && $appnoticeboard->isActive==1) selected @endif>Active</option>
            							<option value="0" @if(isset($appnoticeboard) && $appnoticeboard->isActive==0) selected @endif>Inactive</option>
            							
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
