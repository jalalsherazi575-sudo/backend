@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	/*var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		pageLength: 100,
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
      "emptyTable": "No record found of city."
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
	});*/
	
	
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
	
	if (id!='' && confirm("Are you sure want to delete this city?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/city/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this city because this product category using by vendor.");
					  } else {
					  alert("This City has been successfully deleted.");
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
            <h3 class="page-title">City</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/city/')}}">City</a></li>
                
            </ol>
            <div class="page-actions">
            	@if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                <a href="{{URL::to('admin/city/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New City</a>
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
                        <h6>City</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="row">
                    	<div class="col-sm-12 col-md-6"></div>
                    	  <div class="col-sm-12 col-md-6">
                    	  	 <div class="dataTables_filter" id="users-datatable_filter" style="margin-top: 15px;float: right;margin-right: 20px;">
	                            <form action="{{ URL::to('admin/city/')}}" method="POST" role="search">
	                                  {{ csrf_field() }}
	                               <label>
	                            <input type="text" class="form-control" name="q" placeholder="Search City" value="@if(isset($q)){{$q}}@endif"></label> 

			                    <span class="input-group-btn">
			                    <!-- <button type="submit" name="submit" value="Go" class="btn btn-default">
			                    <span class="glyphicon glyphicon-search"></span>
			                    </button> -->
			                    <input type="submit" name="submit" id="submit" class="btn btn-default" value="Go">
			                    </span>
	                           </form>
	                        </div>   
                        </div>
                    </div>
                    <div class="card-body">
                    	<div>   
                            <div class="row" style="margin-left:0px;">
	                            	<div class="col-lg12 col-md-12 pull-right">
			                    		
			                    		<div class="btnwrp">
			                    		<button class="exportbtn cityrightalign" id="importtocsv" onclick="window.location.href='{{ URL::to("admin/city/importcsv")}}'">Import CSV</button>	
                                		</div>
			                    	</div>	
		                    	
		                    </div>
		                </div> 
					    <form method="post" action="{{ URL::to('admin/city/deleteall')}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								<th width="25%">Country Name</th>
								<th width="25%">State Name</th>
								
								<th width="32%">City Name</th>
								    
                                <th width="12%">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                           @if(isset($city))
                           
                            @foreach($city as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals->id}}"></td>
							    <td>{{ $vals->country }}</td>
							    <td>{{ $vals->state }}</td>
							    <td>{{ $vals->name }}</td>
                                <!-- @if($language)
                                 @foreach($language as $val)
							      <td>{{ Laraspace\Http\Controllers\CommanController::getCityValue($vals->id,$val->id) }}</td>
							      @endforeach
								@endif
								 -->
								<td>
								@if(checkPermission(Auth::user()->id,'update',$currentMenuId))	
								<a href="{{URL::to('admin/city/edit/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>
								@endif
								@if(checkPermission(Auth::user()->id,'delete',$currentMenuId))
                                 <a onclick="return check_delete({{$vals->id}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
								@endif
								
								</td>
							</tr>

                            @endforeach
                            
                            @else
                            <tr><td colspan="6" align="center">No city found.</td></tr>
                            @endif
                            
                            </tbody>
                        </table>
                        @if(isset($city))
                        {!! $city->render() !!}
                        @endif
					  </form>	
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
