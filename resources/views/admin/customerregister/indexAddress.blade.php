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
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                var settings = this.fnSettings();
                var str = settings.oPreviousSearch.sSearch;
                jQuery('.td', nRow).each( function (i) {
                //   this.innerHTML = aData[i].replace( str, '<span class="highlight">'+str+'</span>' );
                } );
                return nRow;
            }
        });
        @if(count($customerAddressData)<10)
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
        <h3 class="page-title">Customer Addresses</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/customers')}}">Customers</a></li>
            <li class="breadcrumb-item active">Customers Addresses</li>
        </ol>
        <div class="page-actions">
            
            @if(checkPermission(Auth::user()->role_id,'create',$currentMenuId))
            <a href="{{URL::to('admin/customer/create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Customer Register</a>
            @endif
            
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h6>All Customer Addresses</h6>
                    <div class="card-actions">
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="80%">
                        <thead>
                            <tr>
                                <th width="30%">Title</th>
                                <th width="10%" style="width:200px !important;">Location</th>
                                <th width="10%">Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($customerAddressData as $customerD)
                        <tr>
                            <td>{{$customerD->title}}</td>
                            <td style="width:200px !important;">{{wordwrap($customerD->location,100)}}</td>
                            
                            <td>{{date(getSettingValue('date_formate'),strtotime($customerD->created_at))}}</td>
                        </tr>
                        @endforeach
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop