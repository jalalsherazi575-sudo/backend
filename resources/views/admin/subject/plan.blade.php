@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
        pageLength: 10,
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [ -1 ] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		//"pageLength": pageLength,
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			var settings = this.fnSettings();
			var str = settings.oPreviousSearch.sSearch;
			jQuery('.td', nRow).each( function (i) {
			 //   this.innerHTML = aData[i].replace( str, '<span class="highlight">'+str+'</span>' );
			} );
			return nRow;
		}
	});
	@if(count($plans)<10)
		$('#users-datatable_paginate').hide();
	@endif
	//table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this admin plan?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/plan/delete/"+id,
            type: "get",
            success: function(data){
                if (data.status == 'success') {
                      alert("This plan has been successfully deleted.");
                      window.location.reload();
                }
            }
        });
    }
}
</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Subjects</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/subject/')}}">Subject</a></li>
                <li class="breadcrumb-item active">Plans</li>
            </ol>
            <div class="page-actions"> 
                 <a href="{{URL::to('admin/planpackage')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Plan</a>
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Plans</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Plan Month</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            @foreach($plans as $pln)
                            <tr>
                                <td>{{$pln->packageName}}</td>
                                <td>{{$pln->packagePeriodInMonth}}</td>
                                <td>{{$pln->packagePrice}}</td>
                                <td>
                                   <!--  <a href="{{URL::to('admin/plan/edit/'.$pln->packageId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a> -->
                                    <a onclick="return check_delete({{$pln->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            @endforeach
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
