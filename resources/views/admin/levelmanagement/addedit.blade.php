@extends('admin.layouts.layout-basic')
@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($levelmanagement)) Edit @else Add @endif Category</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/levelmanagement/')}}">Category Management</a></li>
                <li class="breadcrumb-item active">@if(isset($levelmanagement)) Edit @else Add @endif Category</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="categoryform" enctype="multipart/form-data" method="post" action="@if(isset($levelmanagement)){{ URL::to('admin/levelmanagement/edit/'.$levelmanagement->levelId.'')}}@else{{ URL::to('admin/levelmanagement/add') }}@endif" name="levelmanagement" novalidate>
                    {{csrf_field()}}
					@if(isset($levelmanagement))
                    @method('PUT')
		              <input type="hidden" name="edit_id" value="{{$levelmanagement->levelId}}"> 
	                @endif
					<div class="form-group">
                        <label>Category Name<span class="req">*</span></label>
                        <input type="text" maxlength="50" class="form-control @error('levelName') is-invalid @enderror" value="{{ isset($levelmanagement) ? old('levelName', $levelmanagement->levelName) : old('levelName') }}" name="levelName" placeholder="Basic">
                        @if ($errors->has('levelName'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('levelName') }}
                            </span>
                        @endif
                    </div> <!-- Category Name -->
                    <div class="form-group">
                        <label>Category Image (300px * 300px)<span class="req">*</span></label>
                        @if(isset($levelmanagement))
                        <input type="file"  class="form-control @error('catImage') is-invalid @enderror" name="catImage">
                        @php $filePath = ''; 
                        if(isset($levelmanagement->catImage)){
                            $filePath = public_path('images/category/' . $levelmanagement->catImage);
                        } 
                         @endphp
                        @if(file_exists($filePath))
                            <img height="100" width="100" src="{{asset('images/category/'.$levelmanagement->catImage)}}">
                        @endif
                        @else
                        <input type="file" class="form-control @error('catImage') is-invalid @enderror" name="catImage">
                        @endif
                        <br>
                        @if ($errors->has('catImage'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('catImage') }}
                            </span>
                        @endif
                    </div> <!-- Category Image -->
                    <div class="form-group">
                        <label>Display Rank</label>
                        <input type="text" name="sortOrder" style="width:20%;" maxlength="5" tabindex="3"  id="sortOrder" class="form-control min_width1 @error('sortOrder') is-invalid @enderror" placeholder="Rank" value="@if(isset($levelmanagement)){{$levelmanagement->sortOrder}}@else{{$rank}}@endif{{ old('rank') }}">
                        @if ($errors->has('sortOrder'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('sortOrder') }}
                            </span>
                        @endif
                    </div><!-- Display Rank -->

					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2 @error('status') is-invalid @enderror">
							<option value="1" @if(isset($levelmanagement) && $levelmanagement->isActive==1) selected @endif>Active</option>
							<option value="0" @if(isset($levelmanagement) && $levelmanagement->isActive==0) selected @endif>Inactive</option>
						</select>
                        @if ($errors->has('status'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('status') }}
                            </span>
                        @endif
                    </div><!-- Status -->
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
