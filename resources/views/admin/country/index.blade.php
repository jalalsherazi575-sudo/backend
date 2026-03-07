@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		pageLength: 10,
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [ 0,4] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of country."
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
	@if(count($country)<10)
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
	
	if (id!='' && confirm("Are you sure want to delete this country?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/country/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this country because this product category using by vendor.");
					  } else {
					  alert("This Country has been successfully deleted.");
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
            <h3 class="page-title">Country</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/country/')}}">Country</a></li>
                
            </ol>
            <div class="page-actions">
            	@if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/country/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Country</a>
                @endif
                @if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button>
                @endif

            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Country</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
					    <form method="post" action="{{ URL::to('admin/country/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								
								@if ($language)
									@foreach($language as $val)
								<th width="15%">Name ({{$val->title}})</th>
								    @endforeach
								@endif	
								<th width="15%">Currency</th>
                                <th width="15%">Status</th>
                                <th width="30%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($country as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals->id}}"></td>
                                @if($language)
                                 @foreach($language as $val)
							      <td>{{ Laraspace\Http\Controllers\CommanController::getCountryValue($vals->id,$val->id) }}</td>
							      @endforeach
								@endif
								<td>{{$vals->currency}}</td>
                                @if($vals->status==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif

								<td>
								@if(checkPermission(Auth::user()->id,'update',$currentMenuId))	
								<a href="{{URL::to('admin/country/edit/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
								@endif
								@if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                 <a onclick="return check_delete({{$vals->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                @endif
                                
                                @if(checkPermission(Auth::user()->id,'update',$currentMenuId)) 
                                 @if($vals->status==1)
								<a href="{{URL::to('admin/country/status/0/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
							     @else
								<a href="{{URL::to('admin/country/status/1/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>	 
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
