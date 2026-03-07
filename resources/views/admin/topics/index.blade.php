@extends('admin.layouts.layout-basic')
<?php use Laraspace\Subject; ?>
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
	@if(count($topics)<10)
		$('#users-datatable_paginate').hide();
	@endif
	//table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this topic?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/topics/delete/"+id,
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
            <h3 class="page-title">Topics</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item active">Topics</li>
            </ol>
            <div class="page-actions"> 
                @if (Auth::user()->role=='Administrator')
                 @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/topics/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Topics</a>
                 @endif
                @endif
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Topics</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="7%">ID</th>
                                <th>Topic Name</th>
                                <th width="18%">Subject Name</th>
                                <th  width="18%">Category Name</th>
                                <th width="15%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($topics as $top)
                            <tr>
                                <?php $selcat = Subject::with('category')->find($top->subjectId); 
                               ?>
                                <td>{{$top->id}}</td>
                                <td>{{$top->topicName}}</td>
                                <td>{{$top->subjectName}}</td>
                                <td>{{optional($selcat['category'])->levelName ?? '-'}}</td>
                                <td>
                                    @if(checkPermission(Auth::user()->id,'update',$currentMenuId))
                                    <a href="{{URL::to('admin/topics/edit/'.$top->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                    @endif
                                    
                                    @if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                    <a onclick="return check_delete({{$top->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                    @endif
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
