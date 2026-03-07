@extends('admin.layouts.layout-basic')

@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($banner)) Edit @else Add @endif Banner</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/banner/')}}">Banner</a></li>
            <li class="breadcrumb-item active">@if(isset($banner)) Edit @else Add @endif Banner</li>
        </ol>
    </div>
	<div class="row">
        <div class="col-sm-12">	
            <div class="card center">
                <div class="card-body"> 
                    <form id="bannerform" enctype="multipart/form-data" method="POST" action="@if(isset($banner)){{ URL::to('admin/banner/edit/'.$banner->id.'')}}@else{{ URL::to('admin/banner/add') }}@endif">
                        {{csrf_field()}}
                        @if(isset($banner))
                             @method('PUT')
                        @endif
    					
    					@if(isset($banner))
    		            <input type="hidden" name="edit_id" value="{{$banner->id}}"> 
    	                @endif
    				    
    					<div class="form-group">
                            <label>Title<span class="req">*</span></label>
                            <input type="text" maxlength="50" class="form-control @error('bannerTitle') is-invalid @enderror"  value="{{ isset($banner) ? old('bannerTitle', $banner->bannerTitle) : old('bannerTitle') }}" name="bannerTitle" placeholder="Banner Title">
                             @if ($errors->has('bannerTitle'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('bannerTitle') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Banner Image (500px * 500px)<span class="req">*</span></label>
                            <span>Only supported image: jpeg,png,jpg</span>
                            <input type="file" class="form-control @error('bannerImage') is-invalid @enderror " name="bannerImage" accept="image/*" >
                            @if ($errors->has('bannerImage'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('bannerImage') }}
                                </span>
                            @endif
                            <br>
                            @php $filePath = ''; 
                                if(isset($banner->bannerImage))
                                {
                                    $filePath = public_path('images/banner/' . $banner->bannerImage); 
                                } @endphp
                            @if(file_exists($filePath))
                                <img height="100" width="100" src="{{asset('images/banner/'.$banner->bannerImage)}}">
                            @endif

                          
                        </div>
                        <div class="form-group">
                            <label>Url<span class="req">*</span></label>
                            <input type="text" class="form-control @error('bannerUrl') is-invalid @enderror" value="{{ isset($banner) ? old('bannerUrl', $banner->bannerUrl) : old('bannerUrl') }}" name="bannerUrl" placeholder="Banner Url">
                            @if ($errors->has('bannerUrl'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('bannerUrl') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Start Date<span class="req">*</span></label>
                            <input type="date" class="form-control @error('startDate') is-invalid @enderror"  value="{{ isset($banner) ? old('startDate', $banner->startDate) : old('startDate') }}" name="startDate" placeholder="Start Date">
                            @if ($errors->has('startDate'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('startDate') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>End Date<span class="req">*</span></label>
                            <input type="date" class="form-control @error('endDate') is-invalid @enderror" value="{{ isset($banner) ? old('endDate', $banner->endDate) : old('endDate') }}" name="endDate" placeholder="End Date">
                            @if ($errors->has('endDate'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('endDate') }}
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

