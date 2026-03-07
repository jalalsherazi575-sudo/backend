@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/productcategory/validation.js"></script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">Import Level Management</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/levelmanagement/')}}">Level Management</a></li>
            </ol>
            
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="{{ URL::to('admin/levelmanagement/importcsv') }}" name="levelmanagement" novalidate>
                    {{csrf_field()}}
					
                    <div class="form-group">
                         <label>Import Csv<span class="req">*</span></label>
                         <input type="file" name="import_csv" required="required"  id="import_csv" class="form-control" />
                         <div style="margin-top:5px;"><a  id="download"   href="{{URL::to("exports/levelmanagement-example-import.csv")}}" download/>CSV Import Example</a></div>
                    </div>
                    
                    <button class="btn btn-primary">Submit</button>
                </form>

                <div class="row">
                  @if(isset($finallogs) && !empty($finallogs))
                  <div class="import_logs" style="margin-left:130px;">
                  <h3>Csv Impoted Log File</h3>
                  <p>{!! nl2br($finallogs) !!}</p>
                  </div>
                  @endif
                </div> 
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
