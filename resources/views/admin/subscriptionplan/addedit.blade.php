@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/subscriptionplan/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($subscriptionplan)) Edit @else Add @endif Subscription Plan</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/subscriptionplan/')}}">Subscription Plan</a></li>
                <li class="breadcrumb-item active">@if(isset($subscriptionplan)) Edit @else Add @endif Subscription Plan</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($subscriptionplan)){{ URL::to('admin/subscriptionplan/edit/'.$subscriptionplan->id.'')}}@else{{ URL::to('admin/subscriptionplan/add') }}@endif" name="subscriptionplan" novalidate>
                    {{csrf_field()}}
					
					@if(isset($subscriptionplan))
		            <input type="hidden" name="edit_id" value="{{$subscriptionplan->id}}"> 
	                @endif
					
                    @if ($language)
                      @foreach ($language as $value)
					<div class="form-group">
                        <label>Name ({{$value->title}})<span class="req">*</span></label>
                        <input type="text" maxlength="50" required="required" class="form-control" value="@if(isset($subscriptionplan)){{ Laraspace\Http\Controllers\CommanController::getSubscriptionPlanNameValue($subscriptionplan->id,$value->id) }}@endif" name="name[{{$value->id}}]" placeholder="Plan Name">
                    </div>
                      @endforeach
                    @endif
					
                    <div class="form-group">
                        <label>Number Of Leads Per Month<span class="req">*</span></label>
                        <input type="text" maxlength="5" required="required" class="form-control" value="@if(isset($subscriptionplan) && $subscriptionplan->noOfLeadsPerDuration!=0){{$subscriptionplan->noOfLeadsPerDuration}}@endif" name="noOfLeadsPerDuration" id="noOfLeadsPerDuration" placeholder="Number Of Leads Per Month">
                    </div>

                    <div class="form-group">
                        <label>Price<span class="req">*</span></label>
                        <input type="text" maxlength="5" required="required" class="form-control" value="@if(isset($subscriptionplan) ){{$subscriptionplan->price}}@endif" id="price" name="price" placeholder="Plan Price">
                    </div>
                     
                     @if ($language)
                      @foreach ($language as $value)
                    <div class="form-group">
                        <label>Descripion ({{$value->title}})</label>
                        <textarea name="description[{{$value->id}}]" id="description" class="form-control" cols="10" rows="10" placeholder="Descripion">@if(isset($subscriptionplan)){{ Laraspace\Http\Controllers\CommanController::getSubscriptionPlanDescValue($subscriptionplan->id,$value->id) }}@endif</textarea>
                    </div>
                      @endforeach
                    @endif

					<div class="form-group">
                        <label>Status</label>
						<select name="status" class="form-control ls-select2">
							<option value="1" @if(isset($subscriptionplan) && $subscriptionplan->isActive==1) selected @endif>Active</option>
							<option value="0" @if(isset($subscriptionplan) && $subscriptionplan->isActive==0) selected @endif>Inactive</option>
							
						</select>
                    </div>
                    
                   
                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
