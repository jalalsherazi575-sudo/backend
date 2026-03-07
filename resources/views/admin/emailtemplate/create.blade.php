@extends('admin.layouts.layout-basic')
@section('title')
    Email Template
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script type="text/javascript">
    // Define CKEditor configuration
     var ckeditorConfig = {
            allowedContent: true,
            removeButtons: 'PasteFromWord,PasteText,Paste,About,SpellChecker,Scayt',
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        };

        // Replace CKEditor for 'description' textarea
        CKEDITOR.replace('description', ckeditorConfig);
</script>
@stop

@section('content')
<!-- page content -->
<div class="right_col" role="main">
    <div class="page-header center">
        <h3 class="page-title">Add Email Template</h3>
        <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/email/')}}">Email Template</a></li>
            <li class="breadcrumb-item active">Add Email Template</li>
        </ol>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-sm-12"> 
            <div class="card center">
            
                <div class="card-body">
                    <form id="email"  class="form-horizontal form-label-left"  method="POST"   enctype="multipart/form-data" action="{{route('email.store')}}" >
                        {{csrf_field()}}
                        
                        <div class="form-group">
                            <label>Template Name<span class="req">*</span></label>
                            <input type="text" id="email_name" class="form-control @error('email_name') is-invalid @enderror" name="email_name" value="{{old('email_name')}}" >
                            @if ($errors->has('email_name'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('email_name') }}</strong>
                                </span>
                            @endif
                        </div>  
                        <!-- HTML for Subject dropdown -->
                        <div class="form-group">
                            <label>To<span class="req">*</span></label>
                            <input type="text" id="mail_to" class="form-control @error('mail_to') is-invalid @enderror" name="mail_to" value="{{old('mail_to')}}" >
                            @if ($errors->has('mail_to'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_to') }}</strong>
                                </span>
                            @endif
                        </div><!-- to -->
                        <div class="form-group">
                            <label>CC</label>
                            <input type="text" id="mail_cc" class="form-control @error('mail_cc') is-invalid @enderror" name="mail_cc" value="{{old('mail_cc')}}" >
                            @if ($errors->has('mail_cc'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_cc') }}</strong>
                                </span>
                            @endif
                        </div><!-- cc -->
                        <div class="form-group">
                            <label>BCC</label>
                            <input type="text" id="mail_bcc" class="form-control @error('mail_bcc') is-invalid @enderror" name="mail_bcc" value="{{old('mail_bcc')}}" >
                            @if ($errors->has('mail_bcc'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_bcc') }}</strong>
                                </span>
                            @endif
                        </div><!-- BCC -->
                        <div class="form-group">
                            <label>Subject<span class="req">*</span></label>
                            <input type="text" id="subject" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{old('subject')}}" >
                            @if ($errors->has('subject'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('subject') }}</strong>
                                </span>
                            @endif
                        </div><!-- Subject -->
                        <div class="form-group">
                            <label>Description<span class="req">*</span></label>
                            <textarea type="text" id="description" name="description"  class="form-control ckeditor @error('description') is-invalid @enderror">{{old('description') }}</textarea>
                            @if ($errors->has('description'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('description') }}</strong>
                                </span>
                            @endif
                        </div><!-- Description -->
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
@endsection
