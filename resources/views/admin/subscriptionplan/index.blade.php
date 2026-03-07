@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		pageLength: 10,
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [ 0,4 ] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of subscription plan."
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
	@if(count($subscriptionplan)<10)
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
	
	if (id!='' && confirm("Are you sure want to delete this plan?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/subscriptionplan/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html!=2) {
					  alert(html);
					  } else {
					  alert("This plan has been successfully deleted.");
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
            <h3 class="page-title">Subscription Plans</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/subscriptionplan/')}}">Subscription Plan</a></li>
                
            </ol>
            <div class="page-actions">
                <a href="{{URL::to('admin/subscriptionplan/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Subscription Plan</a>
                <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Subscription Plan</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
					    <form method="post" action="{{ URL::to('admin/subscriptionplan/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								
                               
								<th width="55%">Plan Name</th>
								    

                                <th width="15%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($subscriptionplan as $vals)
                            <tr>
							    <td>
                                    @if ($vals->name!='Free Plan')
							    	<input type="checkbox" class='uniquechk' name='del[]' value="{{$vals->id}}">
							        @endif
							    </td>
                                <td>{{$vals->name}}</td>
                                
                                
                                @if($vals->isActive==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif
							
								<td>
								<a href="{{URL::to('admin/subscriptionplan/edit/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
                                 @if ($vals->name!='Free Plan')
                                 <a onclick="return check_delete({{$vals->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                 @endif
                                
                                @if ($vals->name!='Free Plan')
                                 @if($vals->isActive==1)
								<a href="{{URL::to('admin/subscriptionplan/status/0/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
							     @else
								<a href="{{URL::to('admin/subscriptionplan/status/1/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>	 
							     @endif
							    @endif 
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
