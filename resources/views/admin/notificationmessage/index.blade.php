@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		pageLength: 10,
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [3 ] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of notification message."
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
	@if(count($notificationmessage)<10)
		$('#datatable_paginate').hide();
	@endif
	
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
			if(temp==false)	{
				return false;
			}
			else{
				document.getElementById("delId").value="del";
				document.form.submit();
			}
	}
}

function check_delete(id)
{
	
	if (id!='' && confirm("Are you sure want to delete this Message?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/notificationmessage/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this Message because this Id Proof Type using by vendor.");
					  } else {
					  alert("This Message has been successfully deleted.");
					  window.location.reload();
					  }
				}
        });
	}
	
	
}

</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Notification Message</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/notificationmessage/')}}">Notification Message</a></li>
                
            </ol>
            <div class="page-actions">
                <a href="{{URL::to('admin/notificationmessage/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Message</a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Message</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
					    <form method="post" action="{{ URL::to('admin/notificationmessage/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                
								<th width="25%">Title Key</th>
                                @if ($language)
									@foreach($language as $val)
								<th width="45%">Message ({{$val->title}})</th>
								    @endforeach
								@endif	
                                <th width="10%">Status</th>
                                <th width="10%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($notificationmessage as $vals)
                            <tr>
                                <td>{{$vals['title_key']}}</td>
                                @if($language)
                                 @foreach($language as $val)
							      <td>{{ Laraspace\Http\Controllers\CommanController::getNotificationMessageValue($vals['id'],$val->id) }}</td>
							      @endforeach
								@endif
								
                                @if($vals['isActive']==1)
								<td>Active</td>
							    @else 
                                <td>InActive</td>
							    @endif
								<td>
								<a href="{{URL::to('admin/notificationmessage/edit/'.$vals['id'].'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
								</td>
							</tr>
                            @endforeach
                            <tbody>
                            </tbody>
                        </table>
					  </form>	
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
