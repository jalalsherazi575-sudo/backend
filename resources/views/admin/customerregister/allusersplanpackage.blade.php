@extends('admin.layouts.layout-basic')
@section('scripts')
<script>
    jQuery(document).ready(function () {
        var table = jQuery('#users-datatable').dataTable({
            "order": [[ 1, "desc" ]],
            "lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
            pageLength: 10,
            aoColumnDefs: [
                {  bSortable: false, bSearchable: false, aTargets: [5] },
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
            var tottext = 'entries';
            if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
            if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
            return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
            },
            "language": {
             "emptyTable": "No record found of Plan Package."
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
        @if(count($transactiondetail)<10)
            $('#users-datatable_paginate').hide();
        @endif
        //table.fnSetColumnVis( 0, false );
    });
  
</script>
@stop
@section('content')
<div class="main-content">
    <div class="page-header">
        <h3 class="page-title" style="font-size:24px;">Customer Transaction</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
        <div class="page-actions">
            
            
        <a href="#" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i>All Assign Package</a>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h6>Customer Transaction</h6>
                    <div class="card-actions">
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                               <th width="12%">Transaction Id</th>
                                <th width="11%">Customer Name</th>
                                <th width="11%">Total Amount</th>
                                <th width="12%">Payment Status</th>
                                <th width="12%">Payment Date</th>
                                <th width="8%">Actions</th>
                                </tr>
                        </thead>
                        @foreach($transactiondetail as $detail)
                        <tr>
                            
                            <td>{{$detail['transaction_id'] ?? '-'}}</td>
                            <td style="text-align:center;">{{$detail['name'] ?? '-'}}</td>
                            <td style="text-align:right;">{{$detail['total_amount'] ?? '-'}}</td>
                            <td>
                                @if($detail['payment_status']==1)
                                Paid
                                @elseif($detail['payment_status']==0)
                                Pending
                                @elseif($detail['payment_status']==2)
                                Expire
                                @else
                                    -
                                @endif
                            </td> 
                            <td>
                                @if(!empty($detail['paymentDate']))
                                    {{ date("d/m/Y H:i:s", strtotime($detail['paymentDate'])) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                               <a href="{{URL::to('admin/customer/planpackagedetail/'.$detail->id.'')}}" class="btn btn-default btn-sm" >View Plan Detail</a>
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