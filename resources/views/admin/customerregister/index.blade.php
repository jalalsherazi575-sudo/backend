@extends('admin.layouts.layout-basic')
@section('scripts')
<script>
    jQuery(document).ready(function () {
        var table = jQuery('#users-datatable').dataTable({
            "order": [[ 0, "asc" ]],
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
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                var settings = this.fnSettings();
                var str = settings.oPreviousSearch.sSearch;
                jQuery('.td', nRow).each( function (i) {
                //   this.innerHTML = aData[i].replace( str, '<span class="highlight">'+str+'</span>' );
                } );
                return nRow;
            }
        });
        @if(count($customerData)<10)
            $('#users-datatable_paginate').hide();
        @endif
        //table.fnSetColumnVis( 0, false );
    });
    function check_delete(id)
    {
        if (id!='' && confirm("Are you sure want to delete this customer ?")) {
            window.location.href = "{{URL::to('admin/')}}/customer/deleter/"+id;
        }
    }
</script>
@stop
@section('content')
<div class="main-content">
    <div class="page-header">
        <h3 class="page-title">Customers</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
            <li class="breadcrumb-item active">Customers</li>
        </ol>
        <div class="page-actions">
            
            @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
            <a href="{{URL::to('admin/customer/create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Customer Register</a>
            @endif
            
            <a href="{{URL::to('admin/customer/exportCSV')}}" class="btn btn-primary">Export</a>
        </div>
    </div>
    @include('admin.layouts.partials.flash-message')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h6>All Customers</h6>
                    <div class="card-actions">
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="15%">Name</th>
                                <th width="15%">Email</th>
                                <!-- <th width="10%">Photo</th> -->
                                <th width="12%">Phone</th>
                                <th width="5%">Status</th>
                                <th width="12%">LastLogin Date</th>
                                <th width="28%">Actions</th>
                            </tr>
                        </thead>
                        @foreach($customerData as $customerD)
                        <tr>
                            <td>{{$customerD->name}}</td>
                            <td>{{$customerD->email}}</td>
                            <!-- <td class="table-row-alignment">
                                @if ($customerD->photo!='')
                                <img class="inner-img" src="{{URL::to('customerregisterphoto/thumbnail_images/'.$customerD->photo.'')}}" width="100">
                                @endif
                            </td> -->
                            <td>{{$customerD->phone}}</td>
                            @if($customerD->isActive==1)
                                <td>Active</td>
                                @else 
                                <td>Inactive</td>
                            @endif
                            <td>@if($customerD->lastLoginDate != "" || $customerD->lastLoginDate != null) {{date(getSettingValue('date_formate'),strtotime($customerD->lastLoginDate))}} @endif</td>
                            <td>
                                @if(checkPermission(Auth::user()->id,'update',$currentMenuId))
                                <a href="{{URL::to('admin/customer/editr/'.$customerD->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                @endif
                                
                                @if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                <a onclick="return check_delete({{$customerD->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                @endif

                                @if(checkPermission(Auth::user()->id,'update',$currentMenuId)) 
                                 @if($customerD->isActive==1)
                                <a href="{{URL::to('admin/customer/statusr/0/'.$customerD->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
                                 @else
                                <a href="{{URL::to('admin/customer/statusr/1/'.$customerD->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>    
                                 @endif

                                 <a href="{{URL::to('admin/customer/planpackage/'.$customerD->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>User Subscriptions</a>
                                @endif
                                <a href="{{URL::to('admin/customer/exam/'.$customerD->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Exam</a>
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