@extends('admin.layouts.layout-basic')
@section('scripts')
    <script>
        jQuery(document).ready(function() {
            var table = jQuery('#users-datatable').DataTable({
                processing: true,
                serverSide: true,
                //ajax: '{{ route('admin.questions.data') }}',
                ajax: {
                    url: '{{ route('admin.questions.data') }}',
                    data: function(d) {
                        d.topic = $('select[name="topics"]').val(); // send the selected topic as a parameter
                    }
                },
                columns: [
                    { data: 'select', name: 'select', orderable: false, searchable: false },
                    { data: 'question', name: 'question' },
                    { data: 'isActive', name: 'isActive' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                "order": [[1, "desc"]],
                "lengthMenu": [[-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100]],
                pageLength: 50,
                "fnInfoCallback": function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                    var tottext = 'entries';
                    if(iTotal > 1){ tottext = 'entries'; }else{ tottext = 'entry'; }
                    if(iTotal > 0){iStart = iStart;}else{iStart = 0;}
                    return 'Showing ' + iStart + ' to ' + iEnd + ' of ' + iTotal + ' ' + tottext;
                },
            });
            
           

            // Handle form submission for multiple delete
            $('#uploadForm').on('submit', function(e){
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: "{{ route('importQuestions') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data){
                        if (data.status == 'success') {
                            toastr.success(data.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            });

            // Handle select all
            $('input[name="delall"]').on('click', function() {
                var checkboxes = $('input[name="del[]"]');
                checkboxes.prop('checked', this.checked);
            });

            // Handle individual delete
            window.check_delete = function(id) {
                if (id != '' && confirm("Are you sure want to delete this Question?")) {
                	var selectedTopic = $('select[name="topics"]').val(); 
                    $.ajax({
                        url: "{{ url('admin/questions/delete/') }}/" + id,
                        type: "get",
                         data: {
			                topic: selectedTopic // Pass selected topic ID as data
			            },
                        success: function(data){
                            /*if (data.status == 'success') {
                                toastr.success(data.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error(data.message);
                            }*/
                            table.ajax.reload();
                        }
                    });
                }
            };

            // Handle multiple delete
            window.confirmDelete = function() {
                var form = document.forms['form'];
                var hasChecked = false;
                for (var i = 0; i < form.elements.length; i++) {
                    if (form.elements[i].type == "checkbox" && form.elements[i].checked == true) {
                        hasChecked = true;
                        break;
                    }
                }

                if (!hasChecked) {
                    alert("At least select one record to be deleted..!");
                    return false;
                } else {
                    var confirmation = confirm("Do you really want to delete...!");
                    if (confirmation) {
                        document.getElementById("delId").value = "del";
                        event.preventDefault();
                        var formData = new FormData(form);
                        $.ajax({
                            url: "{{ URL::to('admin/questions/deleteall') }}",
                            type: "post",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(data){
                               /* if (data.status == 'success') {
                                    toastr.success(data.message);
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    toastr.error(data.message);
                                }*/
                                  table.ajax.reload();
                            }
                        });
                    } else {
                        return false;
                    }
                }
            };
           

            /*Import CSV*/
            // Get the file input element
            const fileInput = document.getElementById('csv_file');

            // Add event listener for file selection change
            fileInput.addEventListener('change', function() {
            // Submit the form when a file is selected
            document.getElementById('uploadForm').submit();
            });

             $('#categories').change(function() {
                var category_id = $(this).val();
                
                if (category_id) {
                    $.ajax({
                        url: '/admin/get-subjects/' + category_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#subjects').empty().append('<option value="">Select Subject</option>').show();
                             $('#topics').empty().append('<option value="">Select Topic</option>');
                            $.each(data, function(key, value) {
                                $('#subjects').append('<option value="' + value.id + '">' + value.subjectName + '</option>');
                            });
                        }
                    });
                } else {
                     $('#subjects').hide().empty();
                    $('#topics').hide().empty();
                }
            });

            $('#subjects').change(function() {
                var subject_id = $(this).val();
             
                if (subject_id) {
                    $.ajax({
                        url: '/admin/get-topics/' + subject_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {

                            $('#topics').empty().append('<option value="">Select Topic</option>').show();
                            $.each(data, function(key, value) {
                                $('#topics').append('<option value="' + value.id + '">' + value.topicName + '</option>');
                            });
                            $('#search-button').show();
                        }
                    });
                } else {
                    $('#topics').hide().empty();
                }
            });

            // Handle dropdown change event
            $('#search-button').on('click', function() {
               
                $('#selecttopic').val($('select[name="topics"]').val());
                  table.ajax.reload(null, false);
            });

        });
    
  
    </script>
    <style type="text/css"> .d-flex { display: flex; align-items: center; flex-wrap: wrap; }
        .dropdown-select { eight: 31px; font-size: 1rem; padding: 0 5px; margin-right: 10px; /* Adds space between each select box */ margin-bottom: 10px; /* Adds space below the dropdowns for small screens */ }
        #search-button {height: 31px; font-size: 1rem; padding: 0 10px; }
        @media (max-width: 576px) {
            .d-flex {flex-direction: column;}
            .dropdown-select, #search-button { margin-right: 0; margin-bottom: 10px; /* Adds space below each element in column mode */ }
        }
    </style>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title">Questions</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Questions</li>
            </ol>
            <div class="page-actions ques_listing"> 
                @if(checkPermission(Auth::user()->id,'create',$currentMenuId))
                    <a href="{{ URL::to('admin/questions/add') }}" class="btn btn-primary"><i class="icon-fa icon-fa-plus"></i> New Question</a>
                @endif
                <form id="uploadForm" method="POST" action="{{ route('importQuestions') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="csv_file" class="btn btn-primary"><i class="icon-fa icon-fa-upload"></i> Import Questions</label>
                    <input type="file" id="csv_file" name="question_import" accept=".csv" style="display: none;">
                </form>
                <button onclick="confirmDelete()" class="btn btn-danger"><i class="icon-fa icon-fa-trash"></i> Delete </button>
            </div>
        </div>
        @include('admin.layouts.partials.flash-message')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h6>All Questions</h6>
                        <div class="card-actions"></div>
                    </div>
                    <div class="card-body">
                    	<!-- <select name ="topics" class="form-control col-sm-12 col-md-4" id="topics" style="height: 31px;font-size: 1rem;padding: 0 5px;">
                    		<option value="">select topic</option>
                    		@foreach($topics as $val)
                    		<option value="{{$val->id}}">{{$val->topicName}} - {{$val->subjectName}}</option>
                    		@endforeach
                    	</select> -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select name="categories" class="form-control" id="categories">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->levelId }}">{{ $category->levelName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="subjects" class="form-control" id="subjects" style="display:none;">
                                    <option value="">Select Subject</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="topics" class="form-control" id="topics" style="display:none;">
                                    <option value="">Select Topic</option>
                                </select>
                            </div>
                            <button id="search-button" class="btn btn-primary mb-3" style="display:none;">Search</button>
                        </div>
                        <form method="post" action="{{ URL::to('admin/questions/deleteall') }}" name="form">
                            @csrf
                            <input type="hidden" name="delId" id="delId" value="" />
                            <input type="hidden" name="selecttopic" value="" id="selecttopic">
                            <table id="users-datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th width="8%"><input type="checkbox" name="delall"> Select All</th>
                                    <th>Question</th>
                                    <th width="6%">Status</th>
                                    <th width="28%">Actions</th>
                                </tr>
                                </thead>
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
