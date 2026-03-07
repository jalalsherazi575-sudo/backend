@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		pageLength: 10,
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [ 0,5] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of Lesson management."
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
	@if(count($lessionmanagement)<10)
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
	
	if (id!='' && confirm("Are you sure want to delete this Lesson?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/lessionmanagement/delete/"+id,
        type: "get",
		success: function(html) {
                    alert(html);
			        window.location.reload();
					/*if (html==1) {
					  alert("You can not delete this Lesson because this Lesson using by Level.");
					  } else {
					  alert("This Lesson has been successfully deleted.");
					  window.location.reload();
					  }*/
				}
        });
	}
	
	
}

</script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Lesson Management</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/lessionmanagement/')}}">Lesson Management</a></li>
                
            </ol>
            <div class="page-actions">
            	@if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/lessionmanagement/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Lesson</a>
                @endif
                <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Lesson Management</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
                    	
					    <form method="post" action="{{ URL::to('admin/lessionmanagement/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="2%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								<th width="15%">Lesson Name</th>
								<th width="30%">Lesson Description</th>
                                <th width="11%">Display Rank</th>
                                <th width="12%">Status</th>
                                <th width="17%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($lessionmanagement as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals->lessionId}}"></td>
                                <td>{{ $vals->lessionName }}</td>
                                <td>
                                	@if(strlen($vals->lessionDescription) > 100)
                                	 {{ substr($vals->lessionDescription,0,100)." ......" }}
                                	@else
                                	 {{ $vals->lessionDescription }}
                                	@endif
                                	
                                </td>
                                <td style="text-align:right;">{{ $vals->sortOrder }}</td>
                                @if($vals->isActive==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif
								<td>
								@if(checkPermission(Auth::user()->id,'update',$currentMenuId))	
								<a href="{{URL::to('admin/lessionmanagement/edit/'.$vals->lessionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
								@endif
								@if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                 <a onclick="return check_delete({{$vals->lessionId}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                @endif
                                @if(checkPermission(Auth::user()->id,'update',$currentMenuId)) 
                                 @if($vals->isActive==1)
								<a href="{{URL::to('admin/lessionmanagement/status/0/'.$vals->lessionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
							     @else
								<a href="{{URL::to('admin/lessionmanagement/status/1/'.$vals->lessionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>	 
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
