@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/subject/validation.js"></script>
@stop

@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($subject)) Edit @else Add @endif subject</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/subject/')}}">subject</a></li>
            <li class="breadcrumb-item active">@if(isset($subject)) Edit @else Add @endif subject</li>
        </ol>
    </div>
	<div class="row">
        <div class="col-sm-12">	
            <div class="card center">
            
                <div class="card-body">
                    <form id="subjectform" enctype="multipart/form-data" method="post" action="@if(isset($subject)){{ URL::to('admin/subject/edit/'.$subject->id.'')}}@else{{ URL::to('admin/subject/add') }}@endif" name="subject" >
                        {{csrf_field()}}
    					
    					@if(isset($subject))
    		            <input type="hidden" name="edit_id" value="{{$subject->id}}"> 
    	                @endif
    				    <div class="form-group">
                            <label>Category<span class="req">*</span></label>
                            <select name="category" class="form-control @error('category') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @if($category)
                                    @foreach($category as $vals)
                                    <option value="{{$vals->levelId}}" @if(isset($subject) && $subject->categoryId==$vals->levelId) selected @endif>{{$vals->levelName}}</option>
                                    @endforeach
                                 @endif 
                            </select>
                             @if ($errors->has('category'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('category') }}
                                </span>
                            @endif
                        </div>	
    					<div class="form-group">
                            <label>Subject Name<span class="req">*</span></label>
                            <input type="text" maxlength="50" class="form-control @error('subjectName') is-invalid @enderror"  value="@if(isset($subject)){{$subject->subjectName }}@endif" name="subjectName" placeholder="subject Name">
                            @if ($errors->has('subjectName'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('subjectName') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                        <label>Subject Image (300px * 300px)<span class="req">*</span></label>
                        <input type="file" class="form-control @error('subImage') is-invalid @enderror" name="subImage">
                        @if ($errors->has('subImage'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('subImage') }}
                            </span>
                        @endif
                        <br>
                        @php 
                            $filePath = '';
                            if(isset($subject->subImage)) {
                                $filePath = public_path('images/subject/' . $subject->subImage); 
                            } 
                        @endphp
                        @if(file_exists($filePath))
                            <img height="100" width="100" src="{{asset('images/subject/'.$subject->subImage)}}">
                        @endif
                    </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control ls-select2  @error('status') is-invalid @enderror">
                                <option value="1" @if(isset($subject) && $subject->isActive==1) selected @endif>Active</option>
                                <option value="0" @if(isset($subject) && $subject->isActive==0) selected @endif>Inactive</option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('status') }}
                                </span>
                            @endif
                        </div>
                        <button class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>	  
</div>
@stop

