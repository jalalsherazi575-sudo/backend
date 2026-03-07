@extends('admin.layouts.layout-basic')

@section('scripts')
<script>
	jQuery(document).ready(function () {
		var table = jQuery('#users-datatable').dataTable({
			"order": [[ 0, "desc" ]],
			pageLength: 10,
			"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
			aoColumnDefs: [ 
				{  bSortable: false, bSearchable: false, aTargets: [ 0,3] },
			],
			"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
				var tottext = 'entries';
				if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
				if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
				return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
			},
			"language": {
	      "emptyTable": "No record found of cmspages."
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
		@if(count($cmspages)<10)
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
		
		if (id!='' && confirm("Are you sure want to delete cmspage?")) {
		$.ajax({
	        url: "{{URL::to('admin/')}}/cmspages/delete/"+id,
	        type: "get",
	        
			success: function(data){
					if (data.status == 'success') {
						  alert("This cms page has been successfully deleted.");
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
            <h3 class="page-title">Cms Pages</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/cmspages/')}}">Cms Pages</a></li>
                
            </ol>
            <div class="page-actions">
                <a href="{{URL::to('admin/cmspages/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Cms Page</a>
               
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Cms Page</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
					    <form method="post" action="{{ URL::to('admin/cmspages/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Page Name</th>
                                <th width="9%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($cmspages as $vals)
                            <tr>
							   	<td>{{$vals->name}}</td>
                                @if($vals->isActive==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif
								<td>
								<a href="{{URL::to('admin/cmspages/edit/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>

								<a onclick="return check_delete({{$vals->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" title="Delete Pattern" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>Delete</a>
								
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
