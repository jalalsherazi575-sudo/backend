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
@if(count($users)<10)
	$('#users-datatable_paginate').hide();
@endif
	//table.fnSetColumnVis( 0, false );
});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this admin user?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/users/delete/"+id,
            type: "get",
            success: function(html){
                alert("This admin user has been successfully deleted.");
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
            <h3 class="page-title">Users</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{route('users.index')}}">Users</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
            <div class="page-actions">
                @if (Auth::user()->role=='Administrator')
                 @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/users/create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New User</a>
                 @endif
                @endif
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Users</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th width="15%">Email</th>
                                <th width="14%">Role</th>
                                <th width="14%">Registered On</th>
                                <th width="15%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($users as $user)
                            <tr>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{Laraspace\Http\Controllers\Controller::get_user_role($user->role_id)}}</td>
                                <td>{{date(getSettingValue('date_formate'),strtotime($user->created_at))}}</td>
                                <td>
                                    @if ($user->name!='Consumer Manager')
                                      @if(checkPermission(Auth::user()->id,'update',$currentMenuId))
                                    <a href="{{URL::to('admin/users/edit/'.$user->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                      @endif
                                    
                                    @if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                    <a onclick="return check_delete({{$user->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                    @endif

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
