@extends('admin.layouts.layout-basic')
@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<script type="text/javascript">
  $(document).ready(function () {
        $('.select_all_subject').on('change', function () {
            if ($(this).is(':checked')) {
                $('.remove_all_subject').prop('checked', false);
            }
        });

        $('.remove_all_subject').on('change', function () {
            if ($(this).is(':checked')) {
                $('select[name="select_category[]"]').prop('disabled', true).trigger("chosen:updated");
                $('select[name="select_subject[]"]').prop('disabled', true).trigger("chosen:updated")
                $('.select_all_subject').prop('checked', false);
            }
        });
    });
</script>


<style type="text/css">
    .assi_subject {
    border: 1px solid #E4E4E4;
    border: 1px solid #AAAAAA;
    height: auto;
    width: 100%;
    padding: 5px;
    border-radius: 4px;
    margin-bottom: 10px;
    display: flex;
    flex-wrap: wrap;
}

.inner_assi_cate {
    width: fit-content;
    background-color: #E3E4E4;
    border: 1px solid #AAAAAA;
    border-radius: 4px;
    margin: 3px 5px 3px 0;
    padding: 3px 20px 3px 5px;
    float: left;
    color: #333;
    line-height: 13px;
    cursor: default;
    font-size: 13px;
}
span.remove_cate {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: url(https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen-sprite.png) -40px 3px no-repeat;
}
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $(".chosen-select").chosen({
            no_results_text: "Oops, nothing found!"
        });

        $('.remove_cate').click(function() {
            var id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this item?");

            if (confirmDelete) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{url('admin/remove_subject_plan')}}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        "_token": "{{ csrf_token() }}"
                    }
                }).always(function(data) {
                    if (data.status == 'success') {
                        toastr.success(data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000); // Adjust the timeout as needed
                    } else {
                        toastr.error(data.message);
                    }
                });
            }
        });


        var checked = $('input[name="select_all_subject"]').prop('checked');
        if (checked == false) {
            $('select[name="select_category[]"]').prop('disabled', true).trigger("chosen:updated");
        }else{
            $('select[name="select_category[]"]').prop('disabled', false).trigger("chosen:updated");
        }

        $('.select_all_subject').change(function(){
            if($(this).is(':checked')) {
                // If checkbox is checked, disable the select_subject dropdown
                $('select[name="select_subject[]"]').prop('disabled', true).trigger("chosen:updated");
                $('select[name="select_category[]"]').prop('disabled', false).trigger("chosen:updated");

            } else {
                // If checkbox is unchecked, enable the select_subject dropdown
                $('select[name="select_subject[]"]').prop('disabled', false).trigger("chosen:updated");
                $('select[name="select_category[]"]').prop('disabled', true).trigger("chosen:updated");
            }
        });


        let anyOptionEnabled = true;
    
        // Iterate over each option in the select dropdown
        $('select[name="select_subject[]"] option').each(function() {
            if (!$(this).is(':disabled')) {
                anyOptionEnabled = false;
                return true; // Break out of the loop
            }
        });

        // Set the checkbox based on whether any option is enabled
        if (anyOptionEnabled) {
            $('input[name="select_all_subject"]').prop('checked', true);
            $('select[name="select_subject[]"]').prop('disabled', true).trigger("chosen:updated");
        } else {
            $('input[name="select_all_subject"]').prop('checked', false);
        }


        
    });
</script>
@stop

@section('content')
<div class="main-content">
    <div class="page-header center">
        <h3 class="page-title">Assign Subject</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
            <li class="breadcrumb-item active"><a href="{{URL::to('admin/planpackage')}}">Assign Subject - {{$planpackage->packageName}}</a></li>
        </ol>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card center">
                <div class="card-body">
                    <form id="add_plan" enctype="multipart/form-data" method="post" action="{{ URL::to('admin/planpackage/assingsubjectAdd')}}" name="planpackage">
                        {{csrf_field()}}
                        <input type="hidden" name="plan_id" value="{{$planpackage->packageId}}">
                        @if(!empty($selectedsubject) && count($selectedsubject) != 0)
                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Assigned subjects</label>
                                <div class="col-md-12 col-sm-12">
                                    <div class="assi_subject">
                                        @foreach($selectedsubject as $value)
                                            <div class="inner_assi_cate">
                                                {{ optional(optional($value->subjects)->category)->levelName ?? '-' }}
                                                - {{ optional($value->subjects)->subjectName ?? '-' }}
                                                <span data-id="{{$value->id}}" class="remove_cate"></span>
                                                <input type="hidden" name="select_subject[]" value="{{$value->subject_id}}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif



                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="select_all">Select All Subjects</label>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-check">
                                    <input class="form-check-input select_all_subject" type="checkbox" id="select_all" name="select_all_subject" value="1">
                                </div>
                            </div>
                        </div>

                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="remove_all">Remove All Subjects</label>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-check">
                                    <input class="form-check-input remove_all_subject" type="checkbox" id="remove_all" name="remove_all_subject" value="1">
                                </div>
                            </div>
                        </div>
                        

                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Assign Category</label>
                            <div class="col-md-12 col-sm-12">
                                <select name="select_category[]" class="form-control chosen-select" multiple>
                                    <option disabled>select Category</option>
                                    @foreach($levels as $level)
                                        <option value="{{$level->levelId}}" @if(in_array($level->levelId, $selectedcategory))  disabled=disabled @endif>
                                            {{$level->levelName}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Assign New subjects</label>
                            <div class="col-md-12 col-sm-12">
                                <select name="select_subject[]" class="form-control chosen-select" multiple>
                                    <option disabled>select subject</option>
                                    @foreach($subject as $value)
                                        <option value="{{$value->id}}" @if(in_array($value->id, $selectedsubject->pluck('subject_id')->toArray()))  disabled @endif>
                                            {{$value->category->levelName}}- {{$value->subjectName}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary" style="margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
