@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
		pageLength: 10,
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
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
      "emptyTable": "No product found  of this customer."
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
	@if(count($products)<10)
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
	
	if (id!='' && confirm("Are you sure want to delete this product?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/product/delete/"+id,
        type: "get",
        
		success: function(html){
					if (html==1) {
					  alert("You can not delete this product.");
					  } else {
					  alert("This product has been successfully deleted.");
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
            <h3 class="page-title">Products</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/customer/')}}">Customer</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/customer/')}}">Product</a></li>
                
            </ol>
            
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Products</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body customerpage">
					    <form method="post" action="{{ URL::to('admin/customer/product/deleteall/'.$customerId)}}" name="form">
						<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <input type="hidden" name="delId" id = "delId" value="" />
						<table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="1%"><input type="checkbox" name="delall" onClick="selectall();"></th>
								<th width="15%">Product Name</th>
								<th width="15%">Product Category</th>
								<th width="15%">Description</th>
                                <th width="10%">Product Price</th>
								<th width="5%">Product Condition</th>
                                <th width="5%">Product Status</th>
                                <th width="17%">Actions</th>
                            </tr>
                            </thead>
                            @if(isset($products))
                            @foreach($products as $vals)
                            <tr>
							    <td><input type="checkbox" class='uniquechk' name='del[]' value="{{$vals['productId']}}"></td>
                                <td>{{$vals['productName']}}</td>
								<td>{{$vals['productCategoryName']}}</td>
                                <td>
								{{$vals['productDescription']}}
							   </td>

                                <td> {{$vals['productPrice']}} {{$vals['currency']}}</td>
								<td>{{$vals['prodCondTypeTitle']}}</td>
								<td>
								@if ($vals['productStatus']==1)
								UnSold
								@elseif ($vals['productStatus'] ==2)
                                 Sold
                                @else

                                @endif 
								</td>
                               <td>
								<a href="{{URL::to('admin/customer/product/edit/'.$vals['productId'].'')}}" class="btn btn-default btn-sm" title="Edit Customer"><i class="icon-fa icon-fa-edit"></i></a>
                                 <a onclick="return check_delete({{$vals['productId']}});" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" title="Delete Product" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i></a>
                                 @if($vals['productStatus']==1)
								<a href="{{URL::to('admin/customer/product/status/2/'.$vals['productId'].'')}}" class="btn btn-default btn-sm" title="Inactive Customer"><i class="icon-fa icon-fa-lock"></i></a>
							     @else
								<a href="{{URL::to('admin/customer/status/1/'.$vals['productId'].'')}}" class="btn btn-default btn-sm" title="Active Customer"><i class="icon-fa icon-fa-unlock-alt"></i></a>	 
							     @endif

							     <a href="{{URL::to('admin/customer/product/'.$vals['productId'].'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>View Product Image</a>
								</td>
							</tr>
                            @endforeach
                            @endif
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
