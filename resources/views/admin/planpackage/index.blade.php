@extends('admin.layouts.layout-basic')
@section('scripts')
<script>
    jQuery(document).ready(function () {
        var table = jQuery('#users-datatable').dataTable({
            "order": [[ 4, "desc" ]],
            "lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
            pageLength: 10,
            aoColumnDefs: [
                {  bSortable: false, bSearchable: false, aTargets: [6] },
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
            var tottext = 'entries';
            if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
            if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
            return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
            },
            "language": {
             "emptyTable": "No record found of package."
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
        @if(count($packageData)<10)
            $('#users-datatable_paginate').hide();
        @endif
        //table.fnSetColumnVis( 0, false );
    });

    /*function check_delete(id)
    {
        if (id!='' && confirm("Are you sure want to delete this package?")) {
            window.location.href = "{{URL::to('admin/')}}/planpackage/delete/"+id;
        }
    }*/
    $('.plan_delete').click(function() {
            var id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this plan?");

            if (confirmDelete) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{url('admin/planpackage_delete')}}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        "_token": "{{ csrf_token() }}"
                    }
                }).always(function(data) {
                    if (data.status == 'success') {
                        toastr.success(data.message);
                           setTimeout(function() {
                            location.reload(); // Delay the reload to ensure toastr message is shown
                        }, 1000); // Adjust the delay as necessary
                   } else {
                        toastr.error(data.message);
                    }
                });
            }
        });
</script>
@stop
@section('content')
<div class="main-content">
    <div class="page-header">
        <h3 class="page-title">Subscription Plan</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
            <li class="breadcrumb-item active"><a href="{{URL::to('admin/planpackage')}}">Subscription Plan</a></li>
        </ol>
        <div class="page-actions">
         <a href="{{URL::to('admin/planpackage/create')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Plan</a>   
        </div>
    </div>
    @include('admin.layouts.partials.flash-message')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h6>Subscription Plan</h6>
                    <div class="card-actions">
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="10%">Plan Name</th>
                                <th width="10%">Plan Price</th>
                                <th width="10%">Plan Period In Month</th>
                                <th width="8%">Status</th>
                                <th width="10%">Created Date</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        @foreach($packageData as $packageD)
                        <tr>
                            <td>{{$packageD->packageName}}</td>
                            <td style="text-align:right;">{{$packageD->packagePrice}}</td>
                            <!-- <td>{{$packageD->packageDescription}}</td> -->
                            <td>{{$packageD->packagePeriodInMonth}}</td>
                            <td>
                            @if($packageD->isActive==1)
                                Active
                                @else 
                                Inactive
                            @endif
                            </td>
                            <td>{{date(getSettingValue('date_formate'),strtotime($packageD->createdDate))}}</td>
                            <td>
                                
                                <a href="{{URL::to('admin/planpackage/edit/')}}/{{$packageD->packageId}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Edit</a>
                                
                                 <a data-id="{{$packageD->packageId}}" class="btn btn-default btn-sm plan_delete" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                                                   
                                @if($packageD->isActive==1)
                                <a href="{{URL::to('admin/planpackage/status/0/')}}/{{$packageD->packageId}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
                                @else
                                <a href="{{URL::to('admin/planpackage/status/1/')}}/{{$packageD->packageId}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>    
                                @endif 

                                <a href="{{URL::to('admin/planpackage/assingsubject/')}}/{{$packageD->packageId}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Assign Subject</a>                                        
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