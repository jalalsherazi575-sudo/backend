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
      "emptyTable": "Banner not found."
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
	@if(count($banner)<10)
		$('#datatable_paginate').hide();
	@endif
	
});



function check_delete(id)
{
	
	if (id!='' && confirm("Are you sure want to delete banner?")) {
	$.ajax({
        url: "{{URL::to('admin/')}}/banner/delete/"+id,
        type: "get",
        
		success: function(data){
				if (data.status == 'success') {
					  alert("This banner has been successfully deleted.");
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
            <h3 class="page-title">Banner</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{URL::to('admin/banner/')}}">Banner</a></li>
                
            </ol>
            <div class="page-actions">
                <a href="{{URL::to('admin/banner/add')}}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Banner</a>
                
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Banners</h6>

                        <div class="card-actions">

                        </div>
                    </div>
                    <div class="card-body">
					    <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="15%">Title</th>
								<th width="20%">Url</th>
								<th width="10%">Start Date</th>
								<th width="10%">End Date</th>
								<th width="10%">Banner</th>
                                <th width="12%">Actions</th>
                            </tr>
                            </thead>
                            @foreach($banner as $vals)
                            <tr>
							    <td>{{$vals->bannerTitle}}</td>
                                <td>{{$vals->bannerUrl}}</td>
                                <td>{{$vals->startDate}}</td>
                                <td>{{$vals->endDate}}</td>
                                <?php $url = asset('images/banner/'.$vals->bannerImage);?>
                                <td><img height="50" width="100" src="{{$url}}"></td>
                                <td>
								<a href="{{URL::to('admin/banner/edit/'.$vals->id.'')}}" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>

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
