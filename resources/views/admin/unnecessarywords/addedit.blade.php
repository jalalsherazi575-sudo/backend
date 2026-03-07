@extends('admin.layouts.layout-basic')
@section('title')
    Unnecessary Words
@endsection
@section('content')
 <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($unnecessarywords)) Edit @else Add @endif Unnecessary Words</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                 <li class="breadcrumb-item active"><a href="{{URL::to('admin/unnecessarywords/')}}">Unnecessary Words</a></li>
                <li class="breadcrumb-item active">@if(isset($unnecessarywords)) Edit @else Add @endif Unnecessary Words</li>
            </ol>
        </div>
       <div class="row">
        <div class="col-sm-12"> 
            <div class="card center">
                <div class="card-body">
                    <form id="unnecessarywordsform" enctype="multipart/form-data" method="post" action="@if(isset($unnecessarywords)){{ URL::to('admin/unnecessarywords/update/'.$unnecessarywords->id.'')}}@else{{ URL::to('admin/unnecessarywords/add') }}@endif" name="unnecessarywords" >
                        {{csrf_field()}}
                       <div class="form-group">
                            <label>Words<span class="req">*</span></label>
                            <input type="text" maxlength="50" class="form-control @error('catname') is-invalid @enderror" value="@if(isset($unnecessarywords)){{$unnecessarywords->word }}@endif" name="word" placeholder="Please enter the Words">
                            @if ($errors->has('word'))
                                <span class="error req">
                                    <strong>{{ $errors->first('word') }}</strong>
                                </span>
                            @endif
                        </div>
	                    <input type="submit" name="SubmitVal" value="Submit" class="btn btn-primary">
	                    <!-- <button class="btn btn-primary">Submit</button> -->
                </form>
            </div>
        </div>
      </div>
      </div>      
    </div>
@endsection