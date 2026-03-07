@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/users/users.js"></script>
@stop
@section('content')
    <div class="main-content page-profile">
        <div class="page-header center">
            <h3 class="page-title">Email Template</h3>
            <ol class="breadcrumb">
                 <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{URL::to('admin/email/')}}">Email Template</a></li>
                <li class="breadcrumb-item active">View Email Template</li>
            </ol>
        </div>
        <div class="clearfix"></div>
         <div class="row">
        <div class="col-sm-12"> 
            <div class="card center">
            
                <div class="card-body">
                   <div class="form-group">
                        <label>Template Name<span class="req">*</span></label>
                        <input type="text" id="email_name" class="form-control @error('email_name') is-invalid @enderror" name="email_name" value="{{ old('email_name', $email->email_name) }}"  disabled >
                    </div>  
                    <!-- HTML for Subject dropdown -->
                    <div class="form-group">
                        <label>To<span class="req">*</span></label>
                        <input type="text" id="mail_to" class="form-control @error('mail_to') is-invalid @enderror" name="mail_to" value="{{ old('mail_to', $email->mail_to) }}" disabled>
                    </div><!-- to -->
                    <div class="form-group">
                        <label>CC</label>
                        <input type="text" id="mail_cc" class="form-control @error('mail_cc') is-invalid @enderror" name="mail_cc" value="{{ old('mail_cc', $email->mail_cc) }}" disabled>
                    </div><!-- cc -->
                    <div class="form-group">
                        <label>BCC</label>
                        <input type="text" id="mail_bcc" class="form-control @error('mail_bcc') is-invalid @enderror" name="mail_bcc" value="{{ old('mail_bcc', $email->mail_bcc) }}" disabled>
                    </div><!-- BCC -->
                    <div class="form-group">
                        <label>Subject<span class="req">*</span></label>
                        <input type="text" id="subject" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject', $email->subject) }}" disabled>
                    </div><!-- Subject -->
                    <div class="form-group">
                        <label>Description<span class="req">*</span></label>
                        <label style="background-color: #e9ecef!important;width: 100%;padding-left: 10px;padding-top: 10px;">
                            {!! $email->description !!} 
                        </label>
                    </div><!-- Description -->
                    <div class="form-group">
                        <label>Status</label>
                        <input value="{{ $email->status == 1 ? 'Active' : 'In Active' }}"  class="form-control" disabled>
                    </div> 
                    <button class="btn btn-primary" type="button" onclick="window.location='{{ route("email") }}'">Back</button>
                </div>
            </div>
        </div>
    </div>   
    </div>
@stop
