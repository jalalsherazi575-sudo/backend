@extends('admin.layouts.layout-basic')

@section('scripts')
    <script type="text/javascript">
       $(document).ready(function() {
    $('select[name="category_id"]').on('change', function() {
        $('select[name="plan_id"]').empty().append('<option value="">Select Plan</option>');
        var subjectSelect = $('select[name="subject_id"]');
        subjectSelect.empty().append('<option value="">Select Subject</option>');

        var categoryId = $(this).val();
        if (categoryId) {
            $.ajax({
                url: '{{ route("assignplanpackage.getSubjects", ["categoryId" => ":categoryId"]) }}'.replace(':categoryId', categoryId),
                type: "GET",
                dataType: "json",
                success: function(data) {
                   $.each(data, function(key, value) {
                        subjectSelect.append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        } else {
            $('select[name="subject_id"]').empty();
        }
    });

    $('#subject_id').on('change', function() {
        var subjectId = $(this).val();
        var planSelect = $('select[name="plan_id"]');
        planSelect.empty().append('<option value="">Select Plan</option>');  // Corrected this line

        if (subjectId) {
            $.ajax({
                url: '{{ route("assignplanpackage.getPlans", ["subjectId" => ":subjectId"]) }}'.replace(':subjectId', subjectId),
                type: "GET",
                dataType: "json",
                success: function(data) {
                    
                    $.each(data, function(key, value) {
                        var periodLabel = (value.packagePeriodInMonth === 1) ? ' month' : ' months';
                        var optionText = value.packageName + ' (' + value.packagePeriodInMonth + periodLabel + ': $' + value.packagePrice + ')';
                        planSelect.append('<option value="' + value.packageId + '">' + optionText + '</option>');
                    });
                }
            });
        } else {
            $('select[name="plan_id"]').empty();
        }
    });
});

    </script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">{{$customer->name}} Assign Plan Package</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/customers')}}">Customers</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/customer/planpackage')}}/{{$customer->id}}">{{$customer->name}} of Plan Package Details</a></li>
                <li class="breadcrumb-item active">Assign Plan Package</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="customerplan" enctype="multipart/form-data" method="post" action="{{ URL::to('admin/customer/assignplanpackage/'.$customer->id.'') }}" name="Assign Plan Package" novalidate>
                    {{csrf_field()}}
					<input type="hidden" name="customerid" value="{{$customer->id}}">
					
                    <div class="form-group">
                        <label>Select Category <span class="req">*</span></label>
                        <select name="category_id" required="required" class="form-control ls-select2 @error('category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @if($category)
                               @foreach($category as $cat)
                                <option value="{{$cat->levelId}}">{{$cat->levelName}}</option>
                               @endforeach

                            @endif
                        </select>
                        @if ($errors->has('category_id'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('category_id') }}
                            </span>
                        @endif
                    </div><!-- category_id -->
                    <div class="form-group">
                        <label>Select Subject <span class="req">*</span></label>
                        <select name="subject_id" required="required" class="form-control ls-select2 @error('subject_id') is-invalid @enderror" id="subject_id">
                            <option value="">Select Subject</option>
                        </select>
                        @if ($errors->has('subject_id'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('subject_id') }}
                            </span>
                        @endif
                    </div><!-- subject_id -->

                    <div class="form-group">
                        <label>Select Plan <span class="req">*</span></label>
                        <select name="plan_id" required="required" class="form-control ls-select2 @error('plan_id') is-invalid @enderror">
                            <option value="">Select Plan</option>
                        </select>
                        @if ($errors->has('plan_id'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('plan_id') }}
                            </span>
                        @endif
                    </div><!-- plan_id -->

                    <div class="form-group">
                        <label>Description <span class="req">*</span></label>
                        <textarea name="description" id="description" required="required" maxlength="300" class="form-control @error('description') is-invalid @enderror" rows="5" cols="5"></textarea>
                         @if ($errors->has('description'))
                            <span class="error" style="color:red;">
                                {{ $errors->first('description') }}
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
@stop
