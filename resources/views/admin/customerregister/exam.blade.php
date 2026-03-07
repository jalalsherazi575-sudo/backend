@extends('admin.layouts.layout-basic')

@section('scripts')
<script>
jQuery(document).ready(function () {
    var table = jQuery('#users-datatable').dataTable({
        "order": false,
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
    @if(count($exam)<10)
        $('#users-datatable_paginate').hide();
    @endif
    //table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this Exam?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/customer/exam/delete/"+id,
            type: "get",
            success: function(html){
                alert("This Exam has been deleted successfully.");
                window.location.reload();
            }
        });
    }
}
</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Exam</h3>
            <ol class="breadcrumb">
                 <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/customers/')}}">Customer</a></li>
                <li class="breadcrumb-item active">Exam</li>
            </ol>
           
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Exam</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="15%">Title</th>
								<th width="15%">Question Total</th>
								<th width="15%">Duration Time</th>
                                <th width="17%">Actions</th>
                            </tr>
                            </thead>
                            
                             @if(isset($exam))
                            @foreach($exam as $val)
	                            <tr>
								  
	                                <td>{{$val['title']}}</td>
									<td>{{$val['question_total']}}</td>
	                                <td>{{$val['duration_minutes']}}</td>
									<td>
										<a href="{{URL::to('admin/customer/exam/questions/'.$val->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>View Question
										<a onclick="return check_delete({{ $val->id }});" class="btn btn-default btn-sm" data-token="{{ csrf_token() }}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a></td>
								</tr>
                            @endforeach
                            @endif
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
