@extends('admin.layouts.layout-basic')
@section('scripts')
<script>
    jQuery(document).ready(function () {
        var table = jQuery('#users-datatable').dataTable({
            "order": [[ 1, "desc" ]],
            "lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
            pageLength: 10,
            aoColumnDefs: [
                {  bSortable: false, bSearchable: false, aTargets: [7] },
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
            var tottext = 'entries';
            if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
            if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
            return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
            },
            "language": {
             "emptyTable": "No record found of Plan Package Detail."
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
        @if(count($plandetail)<10)
            $('#users-datatable_paginate').hide();
        @endif
        //table.fnSetColumnVis( 0, false );
    });
    function check_delete(customerId,id)
    {
        if (id!='' && confirm("Are you sure want to delete this Plan Package?")) {
            window.location.href = "{{URL::to('admin/')}}/customer/planpackage/delete/"+customerId+"/"+id;
        }
    }
</script>
@stop
@section('content')
<div class="main-content">
    <div class="page-header">
        <h3 class="page-title" style="font-size:24px;">{{$customer->name}} Of Plan Package Detail</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{URL::to('admin/customers')}}">Customers</a></li>
             <li class="breadcrumb-item"><a href="{{URL::to('admin/customer/planpackage/'.$customer->id.'')}}">Subscriptions</a></li>
            
            <li class="breadcrumb-item active">{{$customer->name}} of Details</li>
        </ol>

    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h6>{{$customer->name}} Of Plan Package Detail</h6>
                    <div class="card-actions">
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                
                                <th width="12%">Transaction Id</th>
                                <th width="11%">Subject</th>
                                <th width="12%">Category</th>
                                <th width="12%">Plan Month</th>
                                <th width="12%">From</th>
                                <th width="12%">To</th>
                                <th width="12%">Amount</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        @foreach($plandetail as $detail)
                        <tr>
                            
                            <td>{{$detail['transaction_order_id'] ?? '-'}}</td>
                            <td>{{optional($detail->subject)->subjectName ?? '-'}}</td>
                            <td>{{optional($detail->category)->levelName ?? '-'}}</td>
                            <td>{{$detail['plan_month'] ?? '-'}}</td>
                           <td>
                                @if(!empty($detail['start_date']))
                                    {{ date("d/m/Y H:i:s", strtotime($detail['start_date'])) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($detail['end_date']))
                                    {{ date("d/m/Y H:i:s", strtotime($detail['end_date'])) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{$detail['plan_amount'] ?? '-'}}</td>
                            <td>
                                @if($detail['status']==1)
                                Paid
                                @elseif($detail['status']==0)
                                Pending
                                @elseif($detail['status']==2)
                                Expire
                                @else
                                    -
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