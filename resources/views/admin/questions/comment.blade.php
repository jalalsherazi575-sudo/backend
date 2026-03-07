@extends('admin.layouts.layout-basic')

@section('scripts')
    <script>
jQuery(document).ready(function () {
	var table = jQuery('#users-datatable').dataTable({
		"order": [[ 0, "desc" ]],
        pageLength: 10,
		"lengthMenu": [[-1,10, 25, 50,100,], ["All",10, 25, 50,100]],
		aoColumnDefs: [ 
			{  bSortable: false, bSearchable: false, aTargets:2},
		],
		"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
			var tottext = 'entries';
			if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
			if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
			return 'Showing '+iStart+' to '+iEnd+' of '+iTotal+' '+tottext;
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
	@if(count($comments)<10)
		$('#users-datatable_paginate').hide();
	@endif
	//table.fnSetColumnVis( 0, false );

    

});

function check_delete(id){
    if (id!='' && confirm("Are you sure want to delete this admin comment?")) {
        $.ajax({
            url: "{{URL::to('admin/')}}/comments/delete/"+id,
            type: "get",
            success: function(data){
                //alert("This Comment has been deleted successfully.");
                if (data.status === 'success') {
                    alert("This Comment has been successfully deleted.");
                    window.location.reload();
                } else {
                     alert("Comment cannot be deleted, as it is used at somewhere!!");
                }
            }
        });
    }
}

/*New Code*/
$(document).ready(function() {
    // Use event delegation for both view-btn and sub-view-btn
    $(document).on('click', '.view-btn, .sub-view-btn', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $parent = $this.closest('li');
        var parentId = $this.data('comment-id');
        var url = '{{ route("childcomment", ":id") }}';
        url = url.replace(':id', parentId);

        var $subCommentsContainer = $parent.find('.subchild, .thirdsubchild');

        if ($subCommentsContainer.is(':visible')) {
            $subCommentsContainer.hide();
        } else {
            $.ajax({
                url: url,
                method: 'GET',
                data: {},
                success: function(response) {
                    if (response.length === 0) {
                        alert('No comments found.');
                        return;
                    }

                    var innerList = '';
                    $.each(response, function(index, item) {
                        innerList += '<li>';
                        innerList += '<div class="left-part">';
                        if (typeof item.customer.photo !== 'undefined' && item.customer.photo !== null) {
                             var imgSrc = '{{ url('/customerregisterphoto/thumbnail_images/') }}' + '/' + item.customer.photo;
                            innerList += '<img src="'+ imgSrc +'">';
                        }
                        innerList += '<h5>' + item.customer.name + '<span>' + item.created_at + '</span></h5>';
                        innerList += '<p>' + item.comment + '</p>';
                        innerList += '</div>';
                        innerList += '<div class="action-btn">';
                        innerList += '<a class="btn btn-default btn-sm sub-view-btn" data-comment-id="' + item.id + '"><i class="icon-fa icon-fa-eye"></i>View More</a>';
                        innerList += ' <a onclick="return check_delete(\'' + item.id + '\');" class="btn btn-default btn-sm" data-token="{{ csrf_token() }}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>';
                        innerList += '</div>';
                        innerList += '<ul class="top-head thirdsubchild" style="display:none;"></ul>';
                        innerList += '</li>';
                    });
                    $subCommentsContainer.html(innerList).show();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    });
});

</script>
@stop
@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Questions</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{url('admin/questions')}}">Questions</a></li>
                <li class="breadcrumb-item active">Comments</li>
            </ol>
       </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Comments</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                      @if($comments->isEmpty())
                              <p class="text-sm-center">No comments available.</p>
                      @else
                          <ul class="top-head">
                             @foreach($comments as $value)
                                <li>
                                  <div class="left-part">
                                    @if(!empty($value->customer->photo) && file_exists(url('customerregisterphoto/thumbnail_images/' . $value->customer->photo)))
                                        <img src="{{ url('/customerregisterphoto/thumbnail_images/' . $value->customer->photo) }}">
                                    @endif

                                  <h5>{{ optional($value->customer)->name }}<span>{{ $value->created_at }} days ago</span></h5>
                                  <p>{!! $value->comment !!}</p>
                                  </div>
                                 <div class="action-btn">
                                    <a class="btn btn-default btn-sm view-btn" data-comment-id="{{ $value->id }}"><i class="icon-fa icon-fa-eye"></i>View More</a> 
                                    @if(checkPermission(Auth::user()->id, 'delete', $currentMenuId))
                                        <a onclick="return check_delete({{ $value->id }});" class="btn btn-default btn-sm" data-token="{{ csrf_token() }}" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>
                                    @endif
                                </div>
                                <ul class="top-head subchild" style="display:none;">
                                    <!-- Second level comment append -->
                                </ul>
                                </li>
                            @endforeach
                          </ul>
                      @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
