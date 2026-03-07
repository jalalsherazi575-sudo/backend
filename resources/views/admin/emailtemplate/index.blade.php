@extends('admin.layouts.layout-basic')
<?php use Laraspace\Subject; ?>
@section('scripts')
<script>
jQuery(document).ready(function () {
    var table = jQuery('#users-datatable').dataTable({
        "order": [[ 1, "desc" ]],
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
    @if(count($emailtemplate)<10)
        $('#users-datatable_paginate').hide();
    @endif
    //table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this email ?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/email/delete/"+id,
            type: "get",
            success: function(html){
                alert("This email has been deleted successfully.");
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
            <h3 class="page-title">Email Templates</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('email/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/email/')}}">Email Templates</a></li>
                
            </ol>
            <!-- <div class="page-actions">
                <a href="{{route('email.create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Email Template</a>
             </div>    -->
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Email Template</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="15%">Email Template Name</th>
                                <th width="15%">To</th>
                                <th width="15%">CC</th>
                                <th width="15%">Subject</th>
                                <th width="15%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                            </thead>
                            </thead>
                            @foreach($emailtemplate as $vals)
                            <tr>
                                
                                <td>{{$vals->email_name}}</td>
                                <td>{{$vals->mail_to}}</td>
                                <td>{{$vals->mail_cc}}</td>
                                <td>{{$vals->subject}}</td>
                                @if($vals->status==1)
                                <td>Active</td>
                                @else 
                                <td>Inactive</td>
                                @endif
                            
                                <td>
                                @if (!empty($vals->id))
                                    <a href="{{ route('email.edit', ['id' => $vals->id]) }}" class="btn btn-default btn-sm">
                                        <i class="icon-fa icon-fa-search"></i>Edit
                                    </a>
                                @endif
                                @if (!empty($vals->id))
                                    <a href="{{ route('email.show', ['id' => $vals->id]) }}" class="btn btn-default btn-sm">
                                        <i class="icon-fa icon-fa-search"></i>view
                                    </a>
                                @endif
                                 <!-- <a onclick="return check_delete({{$vals->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a> -->
                                 
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
