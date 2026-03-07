@extends('admin.layouts.layout-basic')
@section('scripts')
<script src="/assets/admin/js/category/validation.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $("#packagePrice").keypress(function(event){
        var inputValue = event.which;
        
        // Allow only numbers (48-57), space (32), comma (44), and dot (46)
        if (!((inputValue >= 48 && inputValue <= 57) || inputValue == 32 || inputValue == 44 || inputValue == 46)) {
            event.preventDefault();
        }
    });
});


</script> 

@stop
@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">@if(isset($planpackage)) Edit @else Add @endif Subscription Plan</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
             <li class="breadcrumb-item active"><a href="{{URL::to('admin/planpackage')}}">Subscription Plan</a></li>
            
        </ol>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card center">
                <div class="card-body">

                    <form  id="add_plan" enctype="multipart/form-data" method="post" action="@if(isset($planpackage)){{ URL::to('admin/planpackage/update/'.$planpackage->packageId.'')}}@else{{ URL::to('admin/planpackage/store/') }}@endif" name="planpackage" >
                        {{csrf_field()}}

                        <div class="form-group">
                            <label>Plan Name <span class="req">*</span></label>
                            <input type="text"  maxlength="100" class="form-control @error('packageName') is-invalid @enderror"  value="{{ isset($planpackage) ? old('packageName', $planpackage->packageName) : old('packageName') }}" name="packageName"  placeholder="Plan Name">
                            @if ($errors->has('packageName'))
                               <span class="error" style="color:red;">
                                    {{ $errors->first('packageName') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Plan Price <span class="req">*</span></label>
                            <input type="text"   class="form-control @error('packagePrice') is-invalid @enderror" value="{{ isset($planpackage) ? old('packagePrice', $planpackage->packagePrice) : old('packagePrice') }}" name="packagePrice"  placeholder="Plan Price" id="packagePrice">
                            @if ($errors->has('packagePrice'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('packagePrice') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                        <label>Plan Description</label>
                        <textarea  placeholder="Plan Description" class="form-control @error('packageDescription') is-invalid @enderror" id="packageDescription" name="packageDescription">{{ isset($planpackage) ? old('packageDescription', $planpackage->packageDescription) : old('packageDescription') }}</textarea>
                        @if ($errors->has('packageDescription'))
                           <span class="error" style="color:red;">
                                {{ $errors->first('packageDescription') }}
                            </span>
                        @endif
                        </div>

                        <div class="form-group">
                        <label>Plan Period (IN Months)</label>
                        <select name="packagePeriodInMonth" id="packagePeriodInMonth" class="form-control @error('packagePeriodInMonth') is-invalid @enderror" style="width:40%;">
                            <option value="0">Select Months</option>
                             @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (old('packagePeriodInMonth', isset($planpackage) ? $planpackage->packagePeriodInMonth : '') == $i) ? 'selected' : '' }}>
                                    {{ $i }} {{ ($i == 1) ? "Month" : "Months" }}
                                </option>
                            @endfor  
                        </select>
                        @if ($errors->has('packagePeriodInMonth'))
                           <span class="error" style="color:red;">
                                {{ $errors->first('packagePeriodInMonth') }}
                            </span>
                        @endif
                        </div>

                       <!--  <div class="form-group">
                            <label>Android Plan Key</label>
                            <input type="text"  maxlength="120" class="form-control" value="@if(isset($planpackage)){{$planpackage->androidPlanKey}}@endif" name="androidPlanKey"  placeholder="body_1y">
                            
                        </div> -->

                        <!-- <div class="form-group">
                            <label>Ios Plan Key</label>
                            <input type="text"  maxlength="120" class="form-control" value="@if(isset($planpackage)){{$planpackage->iosPlanKey}}@endif" name="iosPlanKey"  placeholder="product_oneyear ">
                            
                        </div> -->
            
                        <div class="form-group">
                            <label>Status</label>
                            <select name="isActive" class="form-control ls-select2 @error('isActive') is-invalid @enderror">
                                <option value="1" {{ old('isActive', (isset($planpackage) && $planpackage->isActive == 1) ? 'selected' : '') }}>Active</option>
                                <option value="0" {{ old('isActive', (isset($planpackage) && $planpackage->isActive == 0) ? 'selected' : '') }}>Inactive</option>
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