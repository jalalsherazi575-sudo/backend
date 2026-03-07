@extends('admin.layouts.layout-basic')
@section('scripts')
<script type="text/javascript">
function checkRadio(vals) {
 
 var optioncount=$('#optioncount2').val();
     
 for(var i=1;i<=optioncount;i++) {          
           
       if ($('#isCorrectAnswer'+i).length) {
           
           if (vals==i) {
            $("#isCorrectAnswer"+vals).prop("checked", true);
            } else {
            $("#isCorrectAnswer"+i).prop("checked", false); 
            }
            
        }

    }
}

</script> 

@stop
@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($planpackage)) Edit @else Add @endif Plan Package</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item "><a href="{{URL::to('admin/subject')}}">Subject</a></li>
             <li class="breadcrumb-item active"><a href="{{URL::to('admin/subject/plans/'.$subId)}}">Plan Package</a></li>
        </ol>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card center">
                <div class="card-body">
                    <form id="subjectplanform" enctype="multipart/form-data" method="post" action="@if(isset($planpackage)){{ URL::to('admin/plan/update/'.$planpackage->packageId.'')}}@else{{ URL::to('admin/plan/store/') }}@endif" name="planpackage" novalidate>
                        {{csrf_field()}}
                        <input type="hidden" name="subId" value="{{$subId}}">
                        <div class="form-group" {{ $errors->has('packageName') ? ' has-error' : '' }}>
                            <label>Package Name <span class="req">*</span></label>
                            <input type="text"  maxlength="100" class="form-control @error('packageName') is-invalid @enderror" value="@if(isset($planpackage)){{$planpackage->packageName}}@endif" name="packageName"  placeholder="Package Name">
                            @if ($errors->has('packageName'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('packageName') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group" {{ $errors->has('packagePrice') ? ' has-error' : '' }}>
                            <label>Package Price <span class="req">*</span></label>
                            <input type="text"  maxlength="6" class="form-control @error('packagePrice') is-invalid @enderror" value="@if(isset($planpackage)){{$planpackage->packagePrice}}@endif" name="packagePrice"  placeholder="Package Price">
                            @if ($errors->has('packagePrice'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('packagePrice') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                        <label>Package Description</label>
                        <textarea  placeholder="Package Description" class="form-control @error('packageDescription') is-invalid @enderror" id="packageDescription" name="packageDescription">@if(isset($planpackage)){{$planpackage->packageDescription}}@endif</textarea>
                        @if ($errors->has('packageDescription'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('packageDescription') }}
                            </span>
                        @endif
                        </div>

                        <div class="form-group">
                            <label>Package Period (IN Months)</label>
                            <select name="packagePeriodInMonth" id="packagePeriodInMonth" class="form-control @error('packagePeriodInMonth') is-invalid @enderror" style="width:40%;">
                                <option value="0">Select Months</option>
                                 <?php for ($i=1; $i<=12; $i++) { ?>
                                <option value="<?php echo $i ?>" @if(isset($planpackage) && $planpackage->packagePeriodInMonth==$i) selected="selected" @endif><?php echo $i ?> 
                                <?php echo ($i == 1) ? "Month" : "Months";?>
                            </option>
                                <?php } ?>    
                            </select>
                             @if ($errors->has('packagePeriodInMonth'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('packagePeriodInMonth') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="isActive" class="form-control ls-select2 @error('isActive') is-invalid @enderror">
                                <option value="1" @if(isset($planpackage) && $planpackage->isActive==1) selected @endif>Active</option>
                                <option value="0" @if(isset($planpackage) && $planpackage->isActive==0) selected @endif>Inactive</option>
                            </select>
                            @if ($errors->has('isActive'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('isActive') }}
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