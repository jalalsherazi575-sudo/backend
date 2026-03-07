@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		pageLength: 10,
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: true, aTargets: [ 0,2,7 ] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of customer."
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
	@if(count($customer)<10)
		$('#users-datatable_paginate').hide();
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
	
	if (id!='' && confirm("Are you sure want to delete this customer?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/customer/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this customer because your lead is ongoing.");
					  } else {
					  alert("This customer has been successfully deleted.");
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
            <h3 class="page-title">Customer</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/customer/')}}">Customer</a></li>
                
            </ol>
            <div class="page-actions">
                <a href="{{URL::to('admin/customer/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Customer</a>
                <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Customer</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body customerpage">
					    <form method="post" action="{{ URL::to('admin/customer/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="1%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								<th width="15%">Name</th>
								<th width="15%">Email</th>
                                <th width="10%">Photo</th>
								<th width="5%">Phone</th>
								<th width="5%">Login Type</th>
                                <th width="5%">Status</th>
                                <th width="5%">Is Verified</th>
                                <th width="17%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($customer as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals['id']}}"></td>
                                <td>{{$vals['fname']." ".$vals['lname']}}</td>
								<td>{{$vals['email']}}</td>
                                <td>
								@if ($vals['photo']!='')
								
							    <img src="{{$vals['photo']}}" width="75px">
							    @endif
							   </td>
                                <td>{{$vals['phone']}}</td>
								@if ($vals['loginType']==1)
								<td>App</td>
                                @elseif ($vals['loginType'] ==2)                                
								<td>Facebook</td>
								@elseif ($vals['loginType'] ==3)
								<td>Google</td>
								@elseif ($vals['loginType'] ==4)
								<td>Twitter</td>
								@else
								<td></td>
                                @endif							
								
                                @if($vals['isActive']==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif

							    @if($vals['isVerify']==1)
								<td>Yes</td>
							    @else 
                                <td>No</td>
							    @endif
							
								<td>
									<div class="dropdown">
  <a class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<span></span><span></span><span></span>
  </a> <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

								<a href="{{URL::to('admin/customer/edit/'.$vals['id'].'')}}" class="btn btn-default btn-sm" title="Edit Customer"><i class="icon-fa icon-fa-edit"></i>Edit</a>
                                 <a onclick="return check_delete({{$vals['id']}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" title="Delete Customer" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>Delete</a>
                                 @if($vals['isActive']==1)
								<a href="{{URL::to('admin/customer/status/0/'.$vals['id'].'')}}" class="btn btn-default btn-sm" title="Inactive Customer"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
							     @else
								<a href="{{URL::to('admin/customer/status/1/'.$vals['id'].'')}}" class="btn btn-default btn-sm" title="Active Customer"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>	 
							     @endif

							     @if($vals['isVerify']==1)
								<a href="{{URL::to('admin/customer/verify/0/'.$vals['id'].'')}}" class="btn btn-default btn-sm" title="Invalidate Customer"><i class="icon-fa icon-fa-lock"></i>Invalidate</a>
							     @else
								<a href="{{URL::to('admin/customer/verify/1/'.$vals['id'].'')}}" class="btn btn-default btn-sm" title="Validate Customer"><i class="icon-fa icon-fa-unlock-alt"></i>Validate</a>	 
							     @endif

							     
							 </div></div>
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
