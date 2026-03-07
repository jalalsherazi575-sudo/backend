@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/topics/validation.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#category').change(function() {
            var categoryId = $(this).val();
            var url = '{{route("topics.cat", ":id")}}';
            url = url.replace(':id',categoryId);
            if (categoryId) {
                $.ajax({
                    type: "GET",
                    url: url, // This is the URL where you fetch subjects based on category ID
                    success: function(subjects) {
                        $('#subject').empty();
                        $('#subject').append('<option value="">Select Subject</option>');
                        $.each(subjects, function(key, value) {
                            $('#subject').append('<option value="' + value.id + '">' + value.subjectName + '</option>');
                        });
                    }
                });
            } else {
                $('#subject').empty();
                $('#subject').append('<option value="">Select Subject</option>');
            }
        });
    });
</script>
@stop

@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($topic)) Edit @else Add @endif Topic</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/topics/')}}">Topics</a></li>
            <li class="breadcrumb-item active">@if(isset($topic)) Edit @else Add @endif Topic</li>
        </ol>
    </div>
	<div class="row">
        <div class="col-sm-12">	
            <div class="card center">
            
                <div class="card-body">
                    <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($topic)){{ URL::to('admin/topics/edit/'.$topic->id.'')}}@else{{ URL::to('admin/topics/add') }}@endif" name="topic" >
                        {{csrf_field()}}
    					
    					@if(isset($topic))
    		            <input type="hidden" name="edit_id" value="{{$topic->id}}"> 
    	                @endif
                        <div class="form-group">
                            <label>Category<span class="req">*</span></label>
                            <select name="category" id="category" class="form-control ls-select2 @error('levelName') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @if($category)
                                    @foreach($category as $vals)
                                    <option value="{{$vals->levelId}}" @if(isset($selcat) && $selcat->categoryId==$vals->levelId) selected @endif>{{$vals->levelName}}</option>
                                    @endforeach
                                @endif 
                            </select>
                            @if ($errors->has('category'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('category') }}
                                </span>
                            @endif
                        </div>	
                        <!-- HTML for Subject dropdown -->
                        <div class="form-group">
                            <label>Subject<span class="req">*</span></label>
                            <select name="subject" class="form-control ls-select2 @error('subject') is-invalid @enderror" id="subject">
                                <option value="">Select Subject</option>
                                @if($subjects)
                                        @foreach($subjects as $subject)
                                            @if(isset($selcat) && $subject->categoryId == $selcat->categoryId)
                                                <option value="{{ $subject->id }}" @if(isset($topic) && $topic->subjectId == $subject->id) selected @endif>
                                                    {{ $subject->subjectName }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                            </select>
                            @if ($errors->has('subject'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('subject') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Topic Name<span class="req">*</span></label>
                            <input type="text" maxlength="50" class="form-control  @error('topicName') is-invalid @enderror"  value="{{ isset($topic) ? old('topicName', $topic->topicName) : old('topicName') }}" name="topicName" placeholder="Topic Name">
                            @if ($errors->has('topicName'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('topicName') }}
                                </span>
                            @endif
                        </div>
                        <!-- <div class="form-group">
                            <label>Topic Description<span class="req">*</span></label>
                            <textarea name="topicDescription" id="topicDescription" required="required" maxlength="300" class="form-control" rows="5" cols="5">@if(isset($topic)){{$topic->topicDescription}}@endif</textarea>
                        </div> -->
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control ls-select2 @error('status') is-invalid @enderror">
                                <option value="1" @if(isset($topic) && $topic->isActive==1) selected @endif>Active</option>
                                <option value="0" @if(isset($topic) && $topic->isActive==0) selected @endif>Inactive</option>
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

