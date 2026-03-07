@extends('admin.layouts.layout-basic')
@section('title')
    Configuration
@endsection

@section('content')
<!-- page content -->
<div class="right_col" role="main">
    <div class="page-header center">
        <h3 class="page-title">Configuration</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item active">Configuration</li>
        </ol>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-sm-12"> 
            <div class="card center">
            
                <div class="card-body">
                   <form id="setting"  class="form-horizontal form-label-left"  method="POST"   enctype="multipart/form-data" action="{{route('defaultconfiguration.store')}}" >
                    {{csrf_field()}}    
                        <input type="hidden" name="setting_id" value="{{$settingdata->id ?? ''}}">
                        <div class="form-group">
                            <label>Mail Host<span class="req">*</span></label>
                            <input type="text" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror" name="mail_host" value="{{ old('mail_host', $settingdata->mail_host ?? '') }}" >
                            @if ($errors->has('mail_host'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_host') }}</strong>
                                </span>
                            @endif
                        </div>  
                        <!-- Mail Host -->
                        <div class="form-group">
                            <label>Mail Username<span class="req">*</span></label>
                            <input type="text" id="mail_username" class="form-control @error('mail_username') is-invalid @enderror" name="mail_username" value="{{ old('mail_username', $settingdata->mail_username ?? '') }}" >
                            @if ($errors->has('mail_username'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_username') }}</strong>
                                </span>
                            @endif
                        </div><!-- Mail Username -->
                        <div class="form-group">
                            <label>Mail Password<span class="req">*</span></label>
                            <input type="password" id="mail_password" class="form-control @error('mail_password') is-invalid @enderror" name="mail_password" value="{{ old('mail_password', $settingdata->mail_password ?? '') }}" >
                            @if ($errors->has('mail_password'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_password') }}</strong>
                                </span>
                            @endif
                        </div><!-- Mail Password -->
                        <div class="form-group">
                            <label>Mail Port<span class="req">*</span></label>
                            <input type="text" id="mail_port" class="form-control @error('mail_port') is-invalid @enderror" name="mail_port" value="{{ old('mail_port', $settingdata->mail_port ?? '') }}" >
                            @if ($errors->has('mail_port'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_port') }}</strong>
                                </span>
                            @endif
                        </div><!-- Mail Port -->
                        <div class="form-group">
                            <label>Mail Encrypion<span class="req">*</span></label>
                            <input type="text" id="mail_encryption" class="form-control @error('mail_encryption') is-invalid @enderror" name="mail_encryption" value="{{ old('mail_encryption', $settingdata->mail_encryption ?? '') }}" >
                            @if ($errors->has('mail_encryption'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_encryption') }}</strong>
                                </span>
                            @endif
                        </div><!-- Subject -->
                        <div class="form-group">
                            <label>Mail From  Name<span class="req">*</span></label>
                            <input type="text" id="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" name="mail_from_name" value="{{ old('mail_from_name', $settingdata->mail_from_name ?? '') }}" >
                            @if ($errors->has('mail_from_name'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_from_name') }}</strong>
                                </span>
                            @endif
                        </div><!-- Description -->
                        <div class="form-group">
                            <label>Mail From  Address<span class="req">*</span></label>
                            <input type="text" id="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" name="mail_from_address" value="{{ old('mail_from_address', $settingdata->mail_from_address ?? '') }}" >
                            @if ($errors->has('mail_from_address'))
                                 <span class="error" style="color:red;">
                                    <strong>{{ $errors->first('mail_from_address') }}</strong>
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
