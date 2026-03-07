@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 4, "desc" ]],
		pageLength: 10,
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets: [ 0,6] },
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
		},
		"language": {
      "emptyTable": "No record found of question."
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
	@if(count($questions)<10)
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
	
	if (id!='' && confirm("Are you sure want to delete this Question?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/questions/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this Question because this Question using by Level.");
					  } else {
					  alert("This Question has been successfully deleted.");
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
            <h3 class="page-title">Questions</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/questions/')}}">Questions</a></li>
                
            </ol>
            <div class="page-actions">
            	@if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/questions/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Question</a>
                @endif
               <!--  <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button> -->
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Questions</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
                    	
					    <form method="post" action="{{ URL::to('admin/questions/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="10%"><input type="checkbox" name="delall" onClick="selectall();"> Select All</th>
								<th width="18%">Question Type</th>
								<th width="12%">Lessson</th>
								<th width="16%">Question</th>
                                <th width="10%">Display Rank</th>
                                <th width="8%">Status</th>
                                <th width="18%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($questions as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals->questionId}}"></td>
							    <td>
                                	@if($vals->questionType==1)
                                      Image Selection
                                	@elseif($vals->questionType==2)
                                      Create Sentence by selecting word 1
                                	@elseif($vals->questionType==3)
                                      Create Sentence by selecting word 2
                                	@elseif($vals->questionType==4)
                                      Fill in the blank
                                	@elseif($vals->questionType==5)
                                      Match the Following
                                	@elseif($vals->questionType==6)
                                      Alphabets & Numbers Learning
                                    @elseif($vals->questionType==7)
                                      Create Video Question
                                	@else

                                	@endif
                                	
                                </td>
                                <td>{{ $vals->lessionName }}</td>
                               
                                <td>{{ $vals->question }}</td>
                                <td style="text-align:right;">{{ $vals->sortOrder }}</td>

                                @if($vals->isActive==1)
								<td>Active</td>
							    @else 
                                <td>Inactive</td>
							    @endif
								
								<td>
								
								@if(checkPermission(Auth::user()->id,'update',$currentMenuId))	
								<a href="{{URL::to('admin/questions/edit/'.$vals->questionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
								@endif
								
								@if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                 <a onclick="return check_delete({{$vals->questionId}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                @endif
                                
                                @if(checkPermission(Auth::user()->id,'update',$currentMenuId)) 
                                 
	                                 @if($vals->isActive==1)
									<a href="{{URL::to('admin/questions/status/0/'.$vals->questionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>
								     @else
									<a href="{{URL::to('admin/questions/status/1/'.$vals->questionId.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>	 
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
