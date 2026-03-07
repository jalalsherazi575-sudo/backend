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
	@if(count($subject)<10)
		$('#users-datatable_paginate').hide();
	@endif
	//table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this admin subject?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/subject/delete/"+id,
            type: "get",
            success: function(data){
                if (data.status == 'success') {
                    toastr.success(data.message);
                       setTimeout(function() {
                        location.reload(); // Delay the reload to ensure toastr message is shown
                    }, 1000); // Adjust the delay as necessary
                } else {
                    toastr.error(data.message);
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
                <li class="breadcrumb-item active">Subject</li>
            </ol>
            <div class="page-actions"> 
                @if (Auth::user()->role=='Administrator')
                 @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/subject/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Subject</a>
                 @endif
                @endif
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Subjects</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                 <th>Category Name</th>
                                <th>Subject Name</th>
                                <th  width="18%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($subject as $sub)
                            <tr>
                                <td>{{optional($sub->category)->levelName}}</td>
                                <td>{{$sub->subjectName}}</td>
                                <td>
                                    @if(checkPermission(Auth::user()->id,'update',$currentMenuId))
                                    <a href="{{URL::to('admin/subject/edit/'.$sub->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                    @endif
                                    
                                    @if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                    <a onclick="return check_delete({{$sub->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                    @endif
                                    <a href="{{URL::to('admin/subject/plans/'.$sub->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Plans</a> 
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
