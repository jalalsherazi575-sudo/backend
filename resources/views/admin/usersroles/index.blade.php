@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
  jQuery('#users-datatable').dataTable({
    "oLanguage": {
        "sEmptyTable": "{{$nouserrolesmsg}}"
    },
    aoColumnDefs: [ 
      {  bSortable: false, bSearchable: false, aTargets: [ -1 ] },
    ],
    "lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
    pageLength: 10,
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
    },
  });
  @if(count($usersroles)<10)
    $('#users-datatable_paginate').hide();
  @endif
  //table.fnSetColumnVis( 0, false );

    });

function check_delete(id)
    {
    if (id!='' && confirm("Are you sure want to delete this user role?")) {
    window.location.href="{{URL::to('admin/')}}/usersroles/delete/"+id;
    /*$.ajax({
        url: "{{URL::to('admin/')}}/usersroles/delete/"+id,
        type: "get",
        
        success: function(html){
                     //if()
                      alert("This admin user has been successfully deleted.");
                      window.location.reload();
                      
                }
        });*/
      }
  }
</script>
@stop

@section('content')

<div class="main-content">
        <div class="page-header">
            <h3 class="page-title">User Roles</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/userroles/')}}">User Roles</a></li>
                
            </ol>
            <div class="page-actions">
              @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/usersroles/create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i>New User Role</a>
              @endif  
                <!-- <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button> -->
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-header">
                        <h6>User Roles</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body vendorpage">

                       <form method="post" action="{{ URL::to('admin/businessusers/deleteall')}}" name="form">
                          <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
                        <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                               <th width="5%">#</th>
                                <th>Role Name</th>
                                <th width="15%">Created On </th>
                                <th width="14%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                             <?php $i=1; ?>
                                  @foreach($usersroles as $user)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$user->role_name}}</td>
                                <td>{{date(getSettingValue('date_formate'),strtotime($user->created_at))}}</td>
                                <td>

                                    
                                        @if(checkPermission(Auth::user()->id,'update',$currentMenuId) || $user->role_name=='Administrator')
                                            <a href="{{URL::to('admin/usersroles/edit/'.$user->role_id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                        @endif

                                       @if ($user->role_name!='Consumer Manager') 
                                            @if ($user->role_name!='Administrator' && checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                            <a onclick="return check_delete({{$user->role_id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                           @endif
                                       @endif       
                                </td>
                           </tr>
                                <?php $i++; ?>
                            @endforeach
                            </tbody>
                            
                        </table>
                       </form>
                    </div>
                </div>
            </div>
        </div>              
 </div>       
       
@stop
