@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/users/users.js"></script>
    <script>
jQuery(document).ready(function () {
    var table = jQuery('#users-datatable').dataTable({
        //"order": [[ 0, "desc" ]],
        "lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
        
        pageLength: 10,
        "bFilter": false,
        "bLengthChange": true,
        aoColumnDefs: [ 
            {  bSortable: false, bSearchable: true, aTargets: [0,6,8,9] },
        ],
        
        "bDestroy": true,
        "language": {
      "emptyTable": "No record found of payout."
         },
        
        //"pageLength": pageLength,
        "paging": true,
        "lengthChange": true,
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
        },

        "processing": true,
        "serverSide": true,
        
        "ajax": "{{URL::to('admin/payoutdata')}}",

        
    });
    @if(count($payout)<10)
        $('#users-datatable_paginate').hide();
    @endif

    $('#search').click( function() {
    var searchtxt=$('#searchbal').val();
        var isActive=$('#isActive').val();
        var startDate=$('#startDate').val();
        var endDate=$('#endDate').val();
        var table = $('#users-datatable').dataTable();
        var res = encodeURIComponent(searchtxt);
        var newUrls="{{URL::to('admin/payoutdata?searchtxt=')}}"+res+"&isActive="+isActive+"&startDate="+startDate+"&endDate="+endDate;
         table.api().ajax.url(newUrls).load();

     });

    $('#exporttoexcel').click(function() {
           var searchtxt=$('#searchbal').val();
           var isActive=$('#isActive').val();
           var startDate=$('#startDate').val();
           var endDate=$('#endDate').val();
           var res = encodeURIComponent(searchtxt);
           var type='xlsx';
           var newUrls="{{URL::to('admin/exportpayoutdata?searchtxt=')}}"+res+"&isActive="+isActive+"&type="+type+"&startDate="+startDate+"&endDate="+endDate;
           window.location.href=newUrls;
     });

    $('#exporttocsv').click(function() {
           var searchtxt=$('#searchbal').val();
           var isActive=$('#isActive').val();
           var startDate=$('#startDate').val();
           var endDate=$('#endDate').val();
           var res = encodeURIComponent(searchtxt);
           var type='csv';
           var newUrls="{{URL::to('admin/exportpayoutdata?searchtxt=')}}"+res+"&isActive="+isActive+"&type="+type+"&startDate="+startDate+"&endDate="+endDate;
           window.location.href=newUrls;
     });



    $('#reset').click( function() {
        $('#searchbal').val('');
        $('#isActive').val('');
        $('#startDate').val('');
        $('#endDate').val('');
        var searchtxt=$('#searchbal').val();
        var isActive=$('#isActive').val();
        var startDate=$('#startDate').val();
        var endDate=$('#endDate').val();
        var table = $('#users-datatable').dataTable();
        var res = encodeURIComponent(searchtxt);
        var newUrls="{{URL::to('admin/payoutdata?searchtxt=')}}"+res+"&isActive="+isActive+"&startDate="+startDate+"&endDate="+endDate;
         table.api().ajax.url(newUrls).load();

     });

     var today = new Date();
$( ".ls-datepicker" ).datepicker(
 {
  changeMonth: true,
  //dateFormat: 'yy-mm-dd',
  dateFormat: 'dd.mm.yy',
  //yearRange: '1950:2006',
  changeYear: true,
  //minDate: 0, 
 // maxDate: '-16Y',
  maxDate: today,
  //maxDate: '-1D',
 // yearRange: "-116:+0"
 }
); 

    
});

function selectall()
{
    
    if(document.form.delall.checked==true)
    {
        var chks = document.getElementsByName('del[]');
        
        for(i=0;i<chks.length;i++)
        {
            chks[i].checked=true;
        }
    }
    else if(document.form.delall.checked==false)
    {
        var chks = document.getElementsByName('del[]');
        
        for(i=0;i<chks.length;i++)
        {
            chks[i].checked=false;
        }
    }
}

function confirmDelete(){
    var f=0;
    var len=document.form.length;
    for(i=1;i<len;i++){
        if(document.form.elements[i].checked==true){
            f=1;
            break;
        }
        else{   
            f=0;
        }
    }
    if(f==0){
        alert("Atleast select one record to be deleted..!");
        return false;
    }
    else{
        var temp=confirm("Do you really want to delete...!");
            if(temp==false) {
                return false;
            }
            else{
                document.getElementById("delId").value="del";
                document.form.submit();
            }
    }
}

function check_cancell(id)
{
    
    if (id!='' && confirm("Are you sure want to cancell this payout request?")) {
    $.ajax({
        url: "{{URL::to('admin/')}}/payoutmanagement/cancellrequest/"+id,
        type: "get",
        
        success: function(html){
                     alert("You have successfully cancelled this payout request.");
                      window.location.reload();
                }
        });
    }
    
    
}

function check_complete(id)
{
    
    if (id!='' && confirm("Are you sure want to complete this payout request?")) {
    $.ajax({
        url: "{{URL::to('admin/')}}/payoutmanagement/completerequest/"+id,
        type: "get",
        success: function(html){
                    
                      alert(html);
                      window.location.reload();
                      
                }
        });
    }
    
    
}









</script>
@stop

@section('content')
    <div class="main-content page-profile">
        <div class="page-header">
            <h3 class="page-title">Payout Management</h3>
            <!-- <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('missionmanagement')}}">Mission Management</a></li>
                <li class="breadcrumb-item active"><a href="@if(isset($mission)){{URL::to('missionmanagement')}}/show/{{$mission['id']}}@endif">@if(isset($mission)) {{$mission['missionName']}}@endif</a></li>
                
            </ol> -->


        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="tabs tabs-default">
                            <?php /* ?><ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link"  href="{{URL::to('admin/missionmanagement/summary/')}}/{{$mission['id']}}" role="tab">Summary</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{URL::to('admin/missionmanagement/show/')}}/{{$mission['id']}}"  role="tab">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{URL::to('admin/missionmanagement/milestones/')}}/{{$mission['id']}}" role="tab">Milestones</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">Enrollments</a>
                                </li>
                                
                            </ul> <?php */ ?>

                            <!-- <div class="page-actions pull-right" style="margin-top:20px;">
                           
                            <button onclick="getProduct1()" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i>Add Milestone</button>
                            </div> -->

                            
                            <!-- Tab panes -->
                            <div class="tab-content milestonedetail">
                                <div class="tab-pane active" id="profile" role="tabpanel">
                                    <div class="row">

                                        <div class="card-body vendorpage">

                                        <div>   
                          
                                            <div class="row" style="margin-left:0px;">

                                              <div class="col-lg3 col-md-3 col-sm-3 col-xs-3">
                                               <input type="text" name="searchbal" id="searchbal" placeholder="Search by Name/Email/Amount" class="form-control" value="">
                                              </div>
                                              
                                               <div class="col-lg2 col-md-2 col-sm-2 col-xs-2">
                                               <select name="isActive" id="isActive"  class="form-control">
                                                        <option value="">Status</option>
                                                        <option value="1">Pending</option>
                                                        <option value="2">Completed</option>
                                                        <option value="3">Cancel</option>
                                                        <!-- <option value="3">Cancelled</option> -->
                                               </select>
                                            </div>
                                            <div class="col-lg2 col-md-2 col-sm-2 col-xs-2">
                                            <input type="text"  readonly="readonly" class="form-control ls-datepicker" value="" name="startDate" id="startDate" placeholder="Start Date">
                                            </div>
                                            <div class="col-lg2 col-md-2 col-sm-2 col-xs-2">
                                            <input type="text"  readonly="readonly" class="form-control ls-datepicker" value="" name="endDate" id="endDate" placeholder="End Date">
                                            </div>    
                                            <div class="col-lg2 col-md-2 col-sm-2 col-xs-2">
                                              <input type="button" name="go" id="search" value="Search" class="btn btn-primary">
                                              <input type="reset" name="reset" id="reset" value="Reset" class="btn btn-danger">
                                               <!-- <div class="btnwrp payoutbtn">
                                               <button class="exportbtn" id="exporttoexcel">Export to Excel</button>
                                               <button class="exportbtn" id="exporttocsv">Export to CSV</button>
                                               </div> -->
                                            </div>  
                                          
                                        </div>

                                        <div class="row exportbtndiv">
                                              <div class="btnwrp payoutbtn">
                                               <button class="exportbtn" id="exporttoexcel">Export to Excel</button>
                                               <button class="exportbtn" id="exporttocsv">Export to CSV</button>
                                               </div>
                                        </div>    

                                      </div> 

                                            <form method="post" action="{{ URL::to('admin/businessusers/deleteall')}}" name="form">
                                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                                <input type="hidden" name="delId" id = "delId" value="" />
                                                <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="10%">First Name</th>
                                                    <th width="10%">Last Name</th>
                                                    <th width="10%">Email</th>
                                                    <th width="15%">Balance Amount (US$)</th>
                                                    <th width="15%">Amount Requested (US$)</th>
                                                    <th width="10%">Payment To</th>
                                                    <th width="10%">Request Date</th>
                                                    <th width="10%">Payout Status</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                                </thead> 
                                                      <?php /* ?><tbody>  
                                                    <?php $i=1; ?>
                                                    @foreach($missionenroll as $vals)
                                                    <tr>
                                                        <td>{{$i}}</td>
                                                        <td>{{ $vals->fname }}</td>
                                                        <td>{{ $vals->lname }}</td>
                                                        <td>{{ $vals->email }}</td>
                                                        <td>
                                                          @if ($vals->status==1)
                                                          Join
                                                          @elseif ($vals->status==2)
                                                          Leave
                                                          @elseif ($vals->status==3)
                                                          Submit
                                                          @elseif ($vals->status==4)
                                                          Completed
                                                          @elseif ($vals->status==5)
                                                          Expired
                                                          @else
                                                          @endif
                                                        </td>
                                                        <td>
                                                        <a href="{{URL::to('admin/missionmanagement/viewdetails/'.$vals->custmissionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>View Details</a>
                                                                        
                                                        </td>
                                                    </tr>
                                                   <?php $i++; ?>
                                                    @endforeach
                                                     </tbody> <?php */ ?>
                                                </table>    
                                             </form>   
                                        </div>   
                                    </div>
                                  
                                </div>
                                <div class="tab-pane" id="messages" role="tabpanel">
                                    
                                </div>
                                <div class="tab-pane" id="friends" role="tabpanel">
                                    <ul class="media-list friends-list">
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar1.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Shane White</h4>
                                                <small>2000 friends</small>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar2.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Adam David</h4>
                                                <small>200 friends</small>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar1.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Shane White</h4>
                                                <small>2000 friends</small>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar2.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Adam David</h4>
                                                <small>200 friends</small>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar1.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Shane White</h4>
                                                <small>2000 friends</small>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <div class="media-left">
                                                <a href="#">
                                                    <img class="media-object" src="/assets/admin/img/avatars/avatar2.png" alt="Generic placeholder image">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">Adam David</h4>
                                                <small>200 friends</small>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
