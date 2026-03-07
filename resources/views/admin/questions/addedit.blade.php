@extends('admin.layouts.layout-basic')

@section('scripts')



<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>

<style type="text/css">
  #answeroptions6,label {font-size:15px;}
</style>

<script type="text/javascript">



    $('#remove_all_option').click(function() {
        $('input[name^="isCorrectAnswer"]').prop('checked', false);
    });
     // Define CKEditor configuration
     var ckeditorConfig = {
            allowedContent: true,
            removeButtons: 'PasteFromWord,PasteText,Paste,About,SpellChecker,Scayt',
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        };

        // Replace CKEditor for 'description' textarea
        CKEDITOR.replace('description', ckeditorConfig);
    $( document ).ready(function() {
        $('#answeroptions').show();
        //$('.type1').prop('required',true); 
        $('.type2').prop('required',false); 
        $('.type3').prop('required',false);
        $('.type4').prop('required',false);
        $('.type5').prop('required',false);
        $('.type6').prop('required',false);
        $('#question').prop('required',true);   
    });

    function addNewOptions() {
        var optioncount=$('#optioncount').val();
        optioncount=parseInt(optioncount) + 1;
        $('#secondansweroptionslist').append('<div  id="optionid'+optioncount+'"><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br>  <input type="text" style="width:90%;" name="questionImageText['+optioncount+']"  maxlength="1000"  id="text'+optioncount+'" class="form-control" placeholder="Melon" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Is Correct Answer</label><br><input type="radio" style="width:40%;" name="isCorrectAnswer['+optioncount+']"    id="isCorrectAnswer'+optioncount+'" class="form-control radioinstant" onclick="return checkRadio('+optioncount+');" value="1"><label>Image</label><input name="optionImage['+optioncount+']"  id="optionImage'+optioncount+'" placeholder="" class="form-control" style="width: 80%;" type="file"></div><span onclick="return removeSelection('+optioncount+');" class="delete_btn">Delete</span></div></div>');
        $('#optioncount').val(optioncount);
    }

    /*$("#categoryId").change(function() {
        var categoryId = $(this).val();
        if(categoryId != ""){
            $.ajax({
                url: "{{URL::to('admin/')}}/questions/getSubject/"+categoryId,
                type: "get",
                success: function(html) {
                    $.each(html, function (key, value) {
                        $("#subjectId").append('<option value="' + value.id + '">' + value.subjectName + '</option>');
                    });
                }
            });
        }else{
            $("#subjectId").html('<option value="">Plese select Subject</option>');
            $("#topicId").html('<option value="">Plese select Topic</option>');
        }
    });

    $("#subjectId").change(function() {
        var topicId = $(this).val();
        if(topicId != ""){
            $.ajax({
                url: "{{URL::to('admin/')}}/questions/getTopic/"+topicId,
                type: "get",
                success: function(html) {
                    $.each(html, function (key, value) {
                        $("#topicId").append('<option value="' + value.id + '">' + value.topicName + '</option>');
                    });
                }
            });
        }else{
            $("#topicId").html('<option value="">Plese select Topic</option>');
        }     
    });*/

    function checkRadio(vals) {
        var optioncount=$('#optioncount').val();
        for(var i=1;i<=optioncount;i++) {
            if ($('#isCorrectAnswer'+i).length) {
                if (vals==i) {
                     $("#isCorrectAnswer"+vals).prop("checked", true);
                } else {
                     $("#isCorrectAnswer"+i).prop("checked", false); 
                }
            }
        }
    }

    function addNewOptions1() {
        var rand= Math.floor((Math.random() * 10000) + 1);
        var optioncount=$('#optioncount').val();
        optioncount=parseInt(optioncount) + 1;
   
        $('#secondansweroptionslist').append('<div  id="optionid'+optioncount+'"><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br>  <input type="text" style="width:90%;" name="questionImageText['+rand+']"  maxlength="1000"  id="text'+optioncount+'" class="form-control" placeholder="Melon" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Is Correct Answer</label><br><input type="radio" style="width:40%;" name="isCorrectAnswer['+rand+']"    id="isCorrectAnswer'+optioncount+'" class="form-control radioinstant" onclick="return checkRadio('+optioncount+');" value="1"></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br><input accept="image/*,video/mp4" name="optionImage['+optioncount+']"  id="optionImage'+optioncount+'" placeholder="" class="form-control" style="width: 80%;" type="file"></div><span onclick="return removeSelection1('+optioncount+');" class="delete_btn">Delete</span></div></div>');
        $('#optioncount').val(optioncount);
      
    }

    function removeSelection1(idvals,optionsId) {
        if (idvals!='' && confirm("Are you sure want to delete this option?")) {
            var optioncount=$('#optioncount').val();
            optioncount=parseInt(optioncount) - 1;
            $('#optioncount').val(optioncount);
            $('#optionid'+idvals).remove();
            var optioncount=$('#optioncount').val();

            $.ajax({
                url: "{{URL::to('admin/')}}/questions/removeOption/"+optionsId,
                type: "get",
                success: function(html) {
                  if (html!='') {
                    alert("You have successfully deleted Picture.");
                    window.location.reload();
                    }
                }
            });
        }
    
    }

    /*Topic search*/
    /*$(document).ready(function() {
    $('#topicId').select2({
        ajax: {
            url: "{{URL::to('admin/')}}/questions/getTopic/",
                type: "get",
              dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        placeholder: 'Search for topics',
        minimumInputLength: 1, // Minimum characters to trigger AJAX request
        multiple: true // Allow multiple selection
    });
     // Preload selected values
    
    var topicId = <?php if(isset($queTopic)){ print_r(json_encode($queTopic)); } else { print_r([]); }?> // Replace with your actual selected values
    if (topicId !== undefined && topicId.length > 0) {
        var $select = $('#topicId');
        $.ajax({
                url: "{{URL::to('admin/')}}/questions/getselectedtopic/"+topicId, // Replace with your actual endpoint URL
                method: 'GET',
                success: function(data) {
                var options = [];
                data.forEach(function(item) {
                    options.push(new Option(item.text, item.id, true, true));
                });
                $select.append(options).trigger('change');
            }
        });
    }
});*/

    $(document).ready(function() {
    $('#topicId').select2({
        ajax: {
            url: "{{URL::to('admin/')}}/questions/getTopic/",
            type: "get",
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        placeholder: 'Search for topics',
        minimumInputLength: 1,
        multiple: true
    });

    // Handle old() values on page reload with validation errors
    var oldValues = @json(old('topicId'));
    if (oldValues && oldValues.length > 0) {
        var $select = $('#topicId');
        $.ajax({
            url: "{{URL::to('admin/')}}/questions/getselectedtopic/" + oldValues.join(','), // Adjust to your endpoint
            method: 'GET',
            success: function(data) {
                var options = [];
                data.forEach(function(item) {
                    options.push(new Option(item.text, item.id, true, true));
                });
                $select.append(options).trigger('change');
            }
        });
    }
});
    

    function removeImage(Id, type) {
    if (Id != '' && confirm("Are you sure you want to delete this picture?")) {
        let url;
        if (type === 'option') {
            url = "{{URL::to('admin/')}}/questions/deleteImages/" + Id;
        } else if (type === 'topic') {
            url = "{{URL::to('admin/')}}/questions/topicImage/" + Id;
        }

        $.ajax({
            url: url,
            type: "get",
            success: function(html) {
                if (html != '') {
                    alert("You have successfully deleted the picture.");
                    window.location.reload();
                }
            }
        });
    }
}

</script>
@stop

@section('content')
    @if (session()->has('flash_notification'))
        @foreach (session('flash_notification') as $message)
            <div class="alert alert-{{ $message->level }}">
                {{ $message->message }}
            </div>
        @endforeach
    @endif
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">@if(isset($questions)) Edit @else Add @endif Question</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/questions/')}}">Questions</a></li>
                <li class="breadcrumb-item active">@if(isset($questions)) Edit @else Add @endif Question</li>
            </ol>
        </div>
       <div class="row">
        <div class="col-sm-12"> 
            <div class="card center">
                <div class="card-body">
                    <form id="questionform" enctype="multipart/form-data" method="post" action="@if(isset($questions)){{ URL::to('admin/questions/edit/'.$questions->questionId.'')}}@else{{ URL::to('admin/questions/add') }}@endif" name="questions" novalidate>
                        {{csrf_field()}}
                        <!-- <div class="form-group">
                            <label>Select Topic<span class="req">*</span></label>
                            <select name="topicId[]" id="topicId" class="form-control ls-select2 @error('topicId') is-invalid @enderror" multiple>
                                <option value="0" disabled>Please select topic</option>
                                
                            </select>
                            @if ($errors->has('topicId'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('topicId') }}
                                </span>
                            @endif

                        </div> --> 

                        <div class="form-group">
                            <label>Select Topic<span class="req">*</span></label>
                            <select name="topicId[]" id="topicId" class="form-control ls-select2 @error('topicId') is-invalid @enderror" multiple>
                                <option value="0" disabled>Please select topic</option>

                                @isset($topics)
                                   @foreach($topics as $top)
                                    <option value="{{$top->id}}" @isset($queTopic) {{ in_array($top->id, $queTopic) ? 'selected' : '' }} @endif>{{$top->topicName}} ({{$top->subjectName}})</option>
                                   @endforeach
                                @endif

                                @if(old('topicId'))
                                    @foreach(old('topicId') as $selected)
                                        <option value="{{ $selected }}">{{ $selected }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @if ($errors->has('topicId'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('topicId') }}
                                </span>
                            @endif
                        </div>   
                        
                        <input type="hidden" name="questionType"  id="questionType" value="1">
                     
                        <div class="form-group" id="listtype"  @if((isset($questions) && $questions->questionType!=6) || !isset($questions)) style="display:none;" @endif>
                            <label>List Type</label>
                            <select name="listType" class="form-control">
                              <option value="1" @if(isset($questions) && $questions->listType==1) selected @endif>Grid</option>
                              <option value="2" @if(isset($questions) && $questions->listType==2) selected @endif>List</option>
                            </select>
                        </div>

                        <div class="form-group" id="ques_name" @if((isset($questions) && $questions->questionType!=5) || !isset($questions))  @else style="display:none;"  @endif>
                            <label>Question Name<span class="req">*</span></label>
                            <input type="text" @if((isset($questions) && $questions->questionType!=5))  @else required="required" @endif class="form-control @error('questions') is-invalid @enderror" value="@if(isset($questions)){{$questions->question}}@endif {{old('question')}}" name="question" id="question">
                            @if ($errors->has('question'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('question') }}
                                </span>
                            @endif
                            <div class="form-group"><label>Question Image</label><br>
                                                <!-- <input name="optionImageee"  id="optionImageee" placeholder="" class="form-control" style="width: 80%;" type="file"> -->
                                                <input type="file" style="width:90%;" accept="image/*,video/mp4" name="topicImage"  maxlength="1000"   id="text1" class="form-control"  value="">
                        </div>
                        @if(isset($questions->video))
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;">
                                                    @php
                                                        $fileExtension = pathinfo($questions->video, PATHINFO_EXTENSION);
                                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif','webp'];
                                                        $videoExtensions = ['mp4', 'avi', 'mov','webp'];
                                                    @endphp
                                                    <!-- @php echo $questions->video; @endphp -->
                                                    @if(in_array($fileExtension, $imageExtensions))
                                                        <img src="{{ URL::to('/') }}/topicImages/{{ $questions->video }}" width="100px" height="100px" style="margin-bottom:10px;">
                                                    @elseif(in_array($fileExtension, $videoExtensions))
                                                        <video width="150" height="150" controls style="margin-bottom:10px;">
                                                            <source src="{{ URL::to('/') }}/topicImages/{{ $questions->video }}" type="video/{{ $fileExtension }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @endif
                                                    @if(in_array($fileExtension, $imageExtensions))
                                                    <br><input type="button"  value="Remove Image" onclick="removeImage({{$questions->questionId}}, 'topic');" class="btn btn-primary" >
                                                    @endif
                                                  </div>
                                                  
                                             </div>
                                             @endif
                        <div class="form-group">
                        </div>
                        
                            <label>Description</label>
                            <textarea name="description" id="description" maxlength="300" class="form-control @error('description') is-invalid @enderror" rows="5" cols="5">@if(isset($questions)){{$questions->description}}@endif {{old('description')}}</textarea>
                            @if ($errors->has('description'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('description') }}
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control ls-select2 @error('status') is-invalid @enderror">
                                <option value="1" @if(isset($questions) && $questions->isActive==1) selected @endif>Active</option>
                                <option value="0" @if(isset($questions) && $questions->isActive==0) selected @endif>Inactive</option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="error" style="color:red;">
                                    {{ $errors->first('status') }}
                                </span>
                            @endif
                        </div>
                        <br>
                        <div class="form-group" id="answeroptions" @if((isset($questions) && $questions->questionType!=1) || !isset($questions)) style="display:none;" @endif>
                            @if (isset($questions) && $questions->questionType==1 && isset($questionimage))
                            <label><span onclick="addNewOptions1();" class="add_btn">Add Options</span></label>
                            <input type="hidden" name="optioncount" id="optioncount" value="{{count($questionimage)}}">
                            <?php $k=1; ?>
                            @foreach($questionimage as $optlist)
                                @if ($k==1)
                                <div id="answeroptionslist">
                                    <div id="optionid1">
                                        <label>Option 1</label>  
                                        <div class="row">
                                            <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Text</label>
                                                <br>  
                                                <input type="text" style="width:90%;" name="questionImageText[{{$optlist->id}}]"  maxlength="1000"   id="text1" class="form-control" placeholder="Melon" value="{{ old('questionImageText.' . $optlist->id, $optlist->questionImageText) }}">
                                            </div>

                                            <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Is Correct Answer</label>
                                                <br>  
                                                <input type="radio" style="width:40%;" name="isCorrectAnswer[{{$optlist->id}}]"    id="isCorrectAnswer1" class="form-control radioinstant" onclick="return checkRadio(1);" @if($optlist->isCorrectAnswer==1) checked="checked" @endif value="1">
                                            </div>
                                            <div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br>
                                                <input type="file" accept="image/*,video/mp4" style="width:90%;" name="optionImage[{{$optlist->id}}]"  maxlength="1000"   id="text1" class="form-control" placeholder="Melon" value="{{$optlist->questionImageText}}">
                                            </div>
                                            @if ($optlist->questionImage!='')
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;">
                                                    @php
                                                        $fileExtension = pathinfo($optlist->questionImage, PATHINFO_EXTENSION);
                                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif','webp'];
                                                        $videoExtensions = ['mp4', 'avi', 'mov','webp'];
                                                    @endphp

                                                    @if(in_array($fileExtension, $imageExtensions))
                                                        <img src="{{ URL::to('/') }}/optionImages/{{ $optlist->questionImage }}" width="100px" height="100px" style="margin-bottom:10px;">
                                                    @elseif(in_array($fileExtension, $videoExtensions))
                                                        <video width="150" height="150" controls style="margin-bottom:10px;">
                                                            <source src="{{ URL::to('/') }}/optionImages/{{ $optlist->questionImage }}" type="video/{{ $fileExtension }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @endif
                                                    <br><input type="button"  value="Remove Image" onclick="removeImage({{$optlist->id}}, 'option');" class="btn btn-primary" >
                                                  </div>
                                                  
                                             </div>
                                             @endif
                                            <span onclick="return removeSelection1({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                        </div>
                                    </div>     
                                </div>
                                @else
                          
                                @if ($k==2)
                                <div id="secondansweroptionslist">
                                @endif
                                <div id="optionid{{$k}}">
                                    <label>Option {{$k}}</label>  
                                    <div class="row">
                                        <div class="col-sm-3 col-md-3 col-lg-3">
                                            <label>Text</label>
                                            <br>  
                                            <input type="text" style="width:90%;" name="questionImageText[{{$optlist->id}}]"  maxlength="1000"  id="text{{$k}}" class="form-control" placeholder="Melon" value="{{ old('questionImageText.' . $optlist->id, $optlist->questionImageText) }}">
                                        </div>

                                        <div class="col-sm-3 col-md-3 col-lg-3">
                                            <label>Is Correct Answer</label>
                                            <br>  
                                            <input type="radio" style="width:40%;" name="isCorrectAnswer[{{$optlist->id}}]"  @if($optlist->isCorrectAnswer==1) checked="checked" @endif  id="isCorrectAnswer{{$k}}" class="form-control radioinstant" onclick="return checkRadio({{$k}});" @if($optlist->isCorrectAnswer==1) checked="checked" @endif value="1">
                                        </div>
                                        <div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br>
                                                <!-- <input name="optionImageee"  id="optionImageee" placeholder="" class="form-control" style="width: 80%;" type="file"> -->
                                                <input type="file" style="width:90%;" name="optionImage[{{$optlist->id}}]"  maxlength="1000"   id="text1" class="form-control" placeholder="Melon" value="{{$optlist->questionImageText}}">
                                            </div>
                                            @if ($optlist->questionImage!='')
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;">
                                                    @php
                                                        $fileExtension = pathinfo($optlist->questionImage, PATHINFO_EXTENSION);
                                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif','webp'];
                                                        $videoExtensions = ['mp4', 'avi', 'mov','webp'];
                                                    @endphp

                                                    @if(in_array($fileExtension, $imageExtensions))
                                                        <img src="{{ URL::to('/') }}/optionImages/{{ $optlist->questionImage }}" width="100px" height="100px" style="margin-bottom:10px;">
                                                    @elseif(in_array($fileExtension, $videoExtensions))
                                                        <video width="150" height="150" controls style="margin-bottom:10px;">
                                                            <source src="{{ URL::to('/') }}/optionImages/{{ $optlist->questionImage }}" type="video/{{ $fileExtension }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @endif
                                                    <br><input type="button"  value="Remove Image" onclick="removeImage({{$optlist->id}}, 'option');" class="btn btn-primary" >
                                                  </div>
                                                  
                                             </div>
                                             @endif
                                        <span onclick="return removeSelection1({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                    </div>
                                </div>  

                                @if ($k==count($questionimage))
                                    </div>
                                    @endif    
                              
                                    @endif

                                    <?php $k++; ?>
                                    @endforeach 
                                        @if(count($questionimage)<=1)
                                       <div id="secondansweroptionslist"></div>
                                        @endif
                                @else
                                    <label><span onclick="return addNewOptions();" class="add_btn">Add Options</span></label>
                                    <input type="hidden" name="optioncount" id="optioncount" value="1">
                                    <div id="answeroptionslist">
                                        <div id="optionid1">
                                            <label>Option 1</label>  
                                            <div class="row">
                                                <div class="col-sm-3 col-md-3 col-lg-3">
                                                    <label>Text</label>
                                                    <br>  
                                                    <input type="text" style="width:90%;" name="questionImageText[1]" @if(!isset($questions))  @endif maxlength="1000"   id="text1" class="form-control" placeholder="Melon" value="{{ old('questionImageText.1', isset($questions) ? $questions->questionImageText : '') }}">
                                                </div>

                                                <div class="col-sm-3 col-md-3 col-lg-3">
                                                    <label>Is Correct Answer</label>
                                                    <br>  
                                                    <input type="radio" style="width:40%;" name="isCorrectAnswer[1]"   id="isCorrectAnswer1" class="form-control radioinstant" onclick="return checkRadio(1);" value="1">
                                                </div>
                                                <div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br>
                                                    <!-- <input name="optionImages"  id="optionImages" placeholder="" class="form-control" style="width: 80%;" type="file"> -->
                                                

                                                    <input type="file" style="width:90%;" name="optionImage[1]" @if(!isset($questions))  @endif maxlength="1000"   id="optionImage" class="form-control" placeholder="Melon" value="">
                                                </div>
                                            </div>
                                        </div>     
                                    </div>
                                   <div id="secondansweroptionslist">
                                   </div>  
                        
                                @endif 
                        </div>

                    <div class="form-group" id="answeroptions2" @if((isset($questions) && $questions->questionType!=2) || !isset($questions)) style="display:none;" @endif>
                     @if (isset($questions) && $questions->questionType==2 && isset($questionwordone))
                       <label><span onclick="addNewOptionsOneWord();" class="add_btn">Add Options</span></label>
                       <h6>Enter Correct answers with sequence numbers.For words which are not part of the sentence, don't provide sequence number</h6>
                       <input type="hidden" name="optioncount2" id="optioncount2" value="{{count($questionwordone)}}">
                        <?php $k=1; ?>
                          @foreach($questionwordone as $optlist)
                           @if ($k==1)
                               <div id="answeroptionslist2">
                                   <div id="optionidword1">
                                     <label>Option 1</label>  
                                          <div class="row">

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="wordText[{{$optlist->id}}]" required="required" maxlength="100"   id="wordText1" class="form-control type2" placeholder="" value="{{ old('wordText.' . $optlist->id, $optlist->wordText) }}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Sequence</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="wordSequence[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequence{{$k}}" class="form-control type2" placeholder="1" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                             </div>

                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br>
                                             <span onclick="return removeSelectionword({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>
                                    </div>     
                               </div>
                           @else
                          
                               @if ($k==2)
                                <div id="secondansweroptionslist2">
                               @endif
                                  <div id="optionidword{{$k}}">
                                    <br>
                                     <label>Option {{$k}}</label>  
                                          <div class="row">
                                             
                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="wordText[{{$optlist->id}}]" required="required" maxlength="100"   id="wordText{{$k}}" class="form-control type2" placeholder="Melon" value="{{ old('wordText.' . $optlist->id, $optlist->wordText) }}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Sequence</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="wordSequence[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequence{{$k}}" class="form-control type2" placeholder="" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                             </div>
                                              
                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br> 
                                              <span onclick="return removeSelectionword({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>

                                    </div>  

                           
                                   @if ($k==count($questionwordone))
                                    </div>
                                   @endif    
                          
                              @endif

                              <?php $k++; ?>
                              @endforeach 
                             
                                  @if(count($questionwordone)<=1)
                                   <div id="secondansweroptionslist2"></div>
                                  @endif
                         

                       @else

                          <label><span onclick="return addNewOptions2();" class="add_btn">Add Options</span></label>
                           <h6>Enter Correct answers with sequence numbers.For words which are not part of the sentence, don't provide sequence number</h6>
                          <input type="hidden" name="optioncount2" id="optioncount2" value="1">
                          
                          <div id="answeroptionslist2">
                             <div id="optionidword1">
                               <label>Option 1</label>  
                                    <div class="row">
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Text</label>
                                        <br>  
                                        <input type="text" style="width:90%;" name="wordText[1]" @if(!isset($questions)) required="required" @endif maxlength="100" id="wordText1" class="form-control type2" placeholder="" value="{{ old('wordText.1', isset($questions) ? $questions->wordText : '') }}">
                                       </div>
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Sequence</label>
                                        <br>  
                                        <input type="text" style="width:80%;" name="wordSequence[1]" @if(!isset($questions)) required="required" @endif maxlength="5" id="wordSequence1" class="form-control type2" placeholder="1" pattern="[0-9]+" value="">
                                       </div>

                                    </div>
                              </div>     
                           </div>

                           <div id="secondansweroptionslist2"></div> 
                      @endif      

                    </div>  

                     <div class="form-group" id="answeroptions3" @if((isset($questions) && $questions->questionType!=3) || !isset($questions)) style="display:none;" @endif>
                     @if (isset($questions) && $questions->questionType==3 && isset($questionwordtwo))
                     
                     <div class="row">

                      <div class="col-sm-6 col-md-6 col-lg-6">

                           <label><span onclick="addNewOptionsTwoWord();" class="add_btn">Add Options</span></label>
                           <h6>Enter Correct answers with sequence numbers.For words which are not part of the sentence, don't provide sequence number</h6>
                           <input type="hidden" name="optioncount3" id="optioncount3" value="{{count($questionwordtwo)}}">
                            <?php $k=1; ?>
                              @foreach($questionwordtwo as $optlist)
                               @if ($k==1)
                                   <div id="answeroptionslist3">
                                       <div id="optionidwordtwo1">
                                         <label>Option 1</label>  
                                              <div class="row">

                                                 <div class="col-sm-6 col-md-6 col-lg-6">
                                                  <label>Text</label>
                                                  <br>  
                                                  <input type="text" style="width:90%;" name="wordTextTwo[{{$optlist->id}}]" required="required" maxlength="100"   id="wordTextTwo1" class="form-control type3" placeholder="" value="{{ old('wordTextTwo.' . $optlist->id, $optlist->wordText) }}">
                                                 </div>

                                                 <div class="col-sm-4 col-md-4 col-lg-4">
                                                  <label>Sequence</label>
                                                  <br>  
                                                  <input type="text" style="width:80%;" name="wordSequenceTwo[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequenceTwo{{$k}}" class="form-control type3" placeholder="1" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                                 </div>

                                                 <div class="col-sm-2 col-md-2 col-lg-2">
                                                  <br>
                                                 <span onclick="return removeSelectionwordTwo({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                                 </div>

                                              </div>
                                        </div>     
                                   </div>
                               @else
                              
                                   @if ($k==2)
                                    <div id="secondansweroptionslist3">
                                   @endif
                                      <div id="optionidwordtwo{{$k}}">
                                        <br>
                                         <label>Option {{$k}}</label>  
                                              <div class="row">
                                                 
                                                 <div class="col-sm-6 col-md-6 col-lg-6">
                                                  <label>Text</label>
                                                  <br>  
                                                  <input type="text" style="width:90%;" name="wordTextTwo[{{$optlist->id}}]" required="required" maxlength="100"   id="wordTextTwo{{$k}}" class="form-control type3" placeholder="Melon" value="{{ old('wordTextTwo.' . $optlist->id, $optlist->wordText) }}">
                                                 </div>

                                                 <div class="col-sm-4 col-md-4 col-lg-4">
                                                  <label>Sequence</label>
                                                  <br>  
                                                  <input type="text" style="width:80%;" name="wordSequenceTwo[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequenceTwo{{$k}}" class="form-control type3" placeholder="" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                                 </div>
                                                  
                                                 <div class="col-sm-2 col-md-2 col-lg-2">
                                                  <br> 
                                                  <span onclick="return removeSelectionwordTwo({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                                 </div>

                                              </div>

                                        </div>  

                               
                                       @if ($k==count($questionwordtwo))
                                        </div>
                                       @endif    
                              
                                  @endif

                                  <?php $k++; ?>
                                  @endforeach 

                                   @if(count($questionwordtwo)<=1)
                                   <div id="secondansweroptionslist3"></div>
                                  @endif

                             </div>

                              <div class="col-sm-6 col-md-6 col-lg-6" id="wordoption2">

                                 <label><span>Add additional information for question explaination</span></label>

                                 <div class="row">
                                    <div class="col-sm-3 col-md-3 col-lg-3">
                                    <label>&nbsp;</label>
                                    </div>
                                    <div class="col-sm-3 col-md-3 col-lg-3">
                                    <label>Heightlight</label>
                                    </div>
                                    <div class="col-sm-5 col-md-5 col-lg-5">
                                    <label>Translation</label>
                                    </div>
                                 </div>
                              
                                  <div id="quesexpl">
                                    @if ((isset($questions) && isset($additional)))
                                      <?php $i=0; ?>
                                      @foreach($additional as $rows)
                                     <div class="row">
  
                                        <div class="col-sm-3 col-md-3 col-lg-3">
                                          <label>{{$rows->originalText}}</label>
                                          <input type="hidden" name="originaltext[{{$rows->id}}]" value="{{$rows->originalText}}">
                                        </div>
                                        
                                        <div class="col-sm-3 col-md-3 col-lg-3">
                                           <label><input type="checkbox" name="heightlight[{{$rows->id}}]" @if($rows->isHeightlight==1) checked="checked" @endif style="width: 70px;margin-top: 5px;" value="1"></label>
                                        </div>
                                        
                                        <div class="col-sm-5 col-md-5 col-lg-5"><label><input type="text" style="width:80%;line-height: 0.5;" name="translation[{{$rows->id}}]"  maxlength="90" id="translation{{$i}}" class="form-control type3" placeholder=""  value="{{$rows->translatedText}}"></label>
                                        </div>

                                   </div>
                                       <?php $i++; ?>
                                     @endforeach
                                   @endif
                                      
                                  </div>     

                              </div> 


                         </div>    
                         

                       @else


                        <div class="row">

                          <div class="col-sm-6 col-md-6 col-lg-6">

                              <label><span onclick="return addNewOptions3();" class="add_btn">Add Options</span></label>
                               <h6>Enter Correct answers with sequence numbers.For words which are not part of the sentence, don't provide sequence number</h6>
                              <input type="hidden" name="optioncount3" id="optioncount3" value="1">
                              
                              <div id="answeroptionslist3">
                                 <div id="optionidwordtwo1">
                                   <label>Option 1</label>  
                                        <div class="row">
                                           <div class="col-sm-6 col-md-6 col-lg-6">
                                            <label>Text</label>
                                            <br>  
                                            <input type="text" style="width:90%;" name="wordTextTwo[1]" @if(!isset($questions)) required="required" @endif maxlength="100" id="wordTextTwo1" class="form-control type3" placeholder="" value="{{ old('wordTextTwo.1', isset($questions) ? $questions->wordTextTwo : '') }}">
                                           </div>
                                           <div class="col-sm-4 col-md-4 col-lg-4">
                                            <label>Sequence</label>
                                            <br>  
                                            <input type="text" style="width:80%;" name="wordSequenceTwo[1]" @if(!isset($questions)) required="required" @endif maxlength="5" id="wordSequenceTwo1" class="form-control type3" placeholder="1" pattern="[0-9]+" value="">
                                           </div>

                                        </div>
                                  </div>     
                               </div>

                               <div id="secondansweroptionslist3"></div>

                         </div>
                         <div class="col-sm-6 col-md-6 col-lg-6" id="wordoption2" style="display:none;">
                               <label><span>Add additional information for question explaination</span></label>
                               <div class="row">
                                  <div class="col-sm-3 col-md-3 col-lg-3">
                                  <label>&nbsp;</label>
                                  </div>
                                  <div class="col-sm-3 col-md-3 col-lg-3">
                                  <label>Heightlight</label>
                                  </div>
                                  <div class="col-sm-5 col-md-5 col-lg-5">
                                  <label>Translation</label>
                                  </div>
                              </div>
                              
                              <div id="quesexpl">
                                  
                             </div>     

                         </div> 

                        </div>        

                      @endif      

                    </div>  

                    <div class="form-group" id="answeroptions4" @if((isset($questions) && $questions->questionType!=4) || !isset($questions)) style="display:none;" @endif>
                     @if (isset($questions) && $questions->questionType==4 && isset($questionfillblank))
                       <label><span onclick="addNewOptionsFillBlank();" class="add_btn">Add Options</span></label>
                       <h6>Add incorrect options. Correct answer will be taken from the word  you</h6>
                       <input type="hidden" name="optioncount4" id="optioncount4" value="{{count($questionfillblank)}}">
                        <?php $k=1; ?>
                          @foreach($questionfillblank as $optlist)
                           @if ($k==1)
                               <div id="answeroptionslist4">
                                   <div id="optionidfillblank1">
                                     <label>Option 1</label>  
                                          <div class="row">

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="optionName[{{$optlist->id}}]"  maxlength="100"   id="wordText1" class="form-control type4" placeholder="" value="{{ old('optionName.' . $optlist->id, $optlist->optionName) }}">
                                             </div>

                                             

                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br>
                                             <span onclick="return removeFillBlankWord({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>
                                    </div>     
                               </div>
                           @else
                          
                               @if ($k==2)
                                <div id="secondansweroptionslist4">
                               @endif
                                  <div id="optionidfillblank{{$k}}">
                                    <br>
                                     <label>Option {{$k}}</label>  
                                          <div class="row">
                                             
                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="optionName[{{$optlist->id}}]" required="required" maxlength="100"   id="optionName{{$k}}" class="form-control type4" placeholder="Melon" value="{{ old('optionName.' . $optlist->id, $optlist->optionName) }}">
                                             </div>

                                             
                                              
                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br> 
                                              <span onclick="return removeFillBlankWord({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>

                                    </div>  

                           
                                   @if ($k==count($questionfillblank))
                                    </div>
                                   @endif    
                          
                              @endif

                              <?php $k++; ?>
                              @endforeach 

                              @if(count($questionfillblank)<=1)
                              <div id="secondansweroptionslist4"></div>
                              @endif

                       @else

                          <label><span onclick="return addNewOptions4();" class="add_btn">Add Options</span></label>
                           <h6>Enter Correct answers with sequence numbers.For words which are not part of the sentence, don't provide sequence number</h6>
                          <input type="hidden" name="optioncount4" id="optioncount4" value="1">
                          
                          <div id="answeroptionslist4">
                             <div id="optionidfillblank1">
                               <label>Option 1</label>  
                                    <div class="row">
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Text</label>
                                        <br>  
                                        <input type="text" style="width:90%;" name="optionName[1]" @if(!isset($questions)) required="required" @endif maxlength="100" id="optionName1" class="form-control type4" placeholder="" value="{{ old('optionName.1', isset($questions) ? $questions->optionName : '') }}">
                                       </div>
                                       
                                    </div>
                              </div>     
                           </div>

                           <div id="secondansweroptionslist4"></div> 
                      @endif      

                    </div>

                    
                    <div class="form-group" id="answeroptions5" @if((isset($questions) && $questions->questionType!=5) || !isset($questions)) style="display:none;" @endif>
                     @if (isset($questions) && $questions->questionType==5 && isset($questionwordmatch))
                       <label><span onclick="addNewOptionsMatch();" class="add_btn">Add Words</span></label>
                       
                       <input type="hidden" name="optioncount5" id="optioncount5" value="{{count($questionwordmatch)}}">
                       <div class="row">
                              <div class="col-sm-3 col-md-3 col-lg-3">
                              <label>English</label>
                              </div>
                              <div class="col-sm-3 col-md-3 col-lg-3">
                              <label>In Haitian Creole</label>
                              </div>
                       </div>

                        <?php $k=1; ?>
                          @foreach($questionwordmatch as $optlist)
                           @if ($k==1)
                               <div id="answeroptionslist5">
                                   <div id="optionidmatch1">
                                       
                                          <div class="row">

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word 1</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="originalWord[{{$optlist->id}}]" required="required" maxlength="100"   id="originalWord1" class="form-control type5" placeholder="" value="{{$optlist->originalWord}}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word 1</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="translateWord[{{$optlist->id}}]" required="required" maxlength="100"   id="translateWord{{$k}}" class="form-control type5" placeholder="1"  value="{{$optlist->translateWord}}">
                                             </div>

                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br>
                                             <span onclick="return removeSelectionMatch({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>
                                    </div>     
                               </div>
                           @else
                          
                               @if ($k==2)
                                <div id="secondansweroptionslist5">
                               @endif
                                  <div id="optionidmatch{{$k}}">
                                          <br>
                                          <div class="row">
                                             
                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word {{$k}}</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="originalWord[{{$optlist->id}}]" required="required" maxlength="100"   id="originalWord{{$k}}" class="form-control type5" placeholder="" value="{{$optlist->originalWord}}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word {{$k}}</label>
                                              <br>  
                                              <input type="text" style="width:90%;" name="translateWord[{{$optlist->id}}]" required="required" maxlength="100"   id="translateWord{{$k}}" class="form-control type5" placeholder=""  value="{{$optlist->translateWord}}">
                                             </div>
                                              
                                             <div class="col-sm-1 col-md-1 col-lg-1">
                                              <br> 
                                              <span onclick="return removeSelectionMatch({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span>
                                             </div>

                                          </div>

                                    </div>  

                           
                                   @if ($k==count($questionwordmatch))
                                    </div>
                                   @endif    
                          
                              @endif

                              <?php $k++; ?>
                              @endforeach 
                             
                                  @if(count($questionwordmatch)<=1)
                                   <div id="secondansweroptionslist5"></div>
                                  @endif
                         

                       @else

                          <label><span onclick="return addNewOptions5();" class="add_btn">Add Words</span></label>
                           
                          <input type="hidden" name="optioncount5" id="optioncount5" value="1">
                          
                          <div class="row">
                              <div class="col-sm-3 col-md-3 col-lg-3">
                              <label>English</label>
                              </div>
                              <div class="col-sm-3 col-md-3 col-lg-3">
                              <label>In Haitian Creole</label>
                              </div>
                          </div>    

                          <div id="answeroptionslist5">
                             <div id="optionidmatch1">
                               
                                    <div class="row">
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Word 1</label>
                                        <br>  
                                        <input type="text" style="width:90%;" name="originalWord[1]" @if(!isset($questions)) required="required" @endif maxlength="100" id="originalWord1" class="form-control type5" placeholder="Water" value="">
                                       </div>
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Word 1</label>
                                        <br>  
                                        <input type="text" style="width:90%;" name="translateWord[1]" @if(!isset($questions)) required="required" @endif maxlength="100" id="translateWord1" class="form-control type5" placeholder="dlo"  value="">
                                       </div>

                                    </div>
                              </div>     
                           </div>

                           <div id="secondansweroptionslist5"></div> 
                      @endif      

                    </div>   

                    

                    <div class="form-group" id="answeroptions6" @if((isset($questions) && $questions->questionType!=6) || !isset($questions)) style="display:none;" @endif>

                    @if (isset($questions) && $questions->questionType==6 && isset($pronounciationFile))
                       <label><span onclick="addNewOptionsAl();" class="add_btn">Add Options</span></label>
                         <input type="hidden" name="optioncount6" id="optioncount6" value="{{count($pronounciationFile)}}">
                        <?php $k=1; ?>
                          @foreach($pronounciationFile as $optlist)
                           @if ($k==1)
                               <div id="answeroptionslist6">
                                   <div id="optionidal1">
                                     <label>Option 1</label>  
                                          <div class="row">
                                             
                                             <div class="col-sm-2 col-md-2 col-lg-2">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text"  name="optionNameAl[{{$optlist->id}}]"  maxlength="100"   id="optionNameAl1" class="form-control" placeholder="" value="{{ old('optionNameAl.' . $optlist->id, $optlist->optionName) }}">
                                             </div>

                                              <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                              <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Image</label> 
                                                <br> 
                                                <!-- <input name="optionImages"  id="optionImages" placeholder="" class="form-control" style="width: 80%;" type="file"> -->
                                                 <input type="text"  name="optionNameAl1[{{$optlist->id}}]"  maxlength="100"   id="optionNameAl2" class="form-control" placeholder="" value="{{$optlist->optionName}}">
                                                 
                                                  @if ($optlist->optionImages!='')
                                                   <br>
                                                   <div class="row">
                                                        <div style="margin-left:25px;margin-bottom:10px;">
                                                          <a href="{{ URL::to('/')}}/optionImages/{{$optlist->optionImages}}" target="_blank">Option Image</a>
                                                          
                                                          <br><input type="button" name="removeProof" id="remove" value="Remove File" onclick="removeAlphaFile({{$optlist->id}});" class="btn btn-primary" >
                                                        </div>
                                                        
                                                   </div>
                                                  @endif
                                                 
                                              </div>

                                              <div class="col-sm-2 col-md-2 col-lg-2">
                                                  <label>Sequence</label>
                                                  <br>  
                                                  <input type="text" style="width:80%;" name="wordSequenceAl[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequenceAl{{$k}}" class="form-control" placeholder="" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                              </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Upload Pronunciation File</label>
                                              <br>  
                                              <input type="file" style="width:100%;" name="pronounciationFile[{{$optlist->id}}]"   id="pronounciationFile1" class="form-control" placeholder="Pronunciation File">

                                              @if ($optlist->pronounciationFile!='')
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;">
                                                    <a href="{{ URL::to('/')}}/pronounciationFile/{{$optlist->pronounciationFile}}" target="_blank">Pronunciation File</a>
                                                    
                                                    <br><input type="button" name="removeProof" id="remove" value="Remove File" onclick="removePronounciationFile({{$optlist->id}});" class="btn btn-primary" >
                                                  </div>
                                                  
                                             </div>
                                             @endif

                                             </div>
                                             
                                             <div class="col-sm-1 col-md-1 col-lg-1" style="margin-top:25px;"><span onclick="return removeAl({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span></div>

                                             

                                             
                                              
                                          </div>
                                    </div>     
                               </div>
                           @else
                          
                               @if ($k==2)
                                <div id="secondansweroptionslist6">
                               @endif
                                  <div id="optionidal{{$k}}">
                                     <label>Option {{$k}}</label>  
                                          <div class="row">
                                             
                                             <div class="col-sm-2 col-md-2 col-lg-2">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text"  name="optionNameAl[{{$optlist->id}}]"  maxlength="100"   id="optionNameAl{{$k}}" class="form-control" placeholder="" value="{{ old('optionNameAl.' . $optlist->id, $optlist->optionName) }}">
                                             </div>
                                              
                                              <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                               <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Image</label> 
                                                <br> 
                                                <input name="optionImages"  id="optionImages" placeholder="" class="form-control" style="width: 80%;" type="file">
                                                 
                                                  @if ($optlist->optionImages!='')
                                                   <br>
                                                   <div class="row">
                                                        <div style="margin-left:25px;margin-bottom:10px;">
                                                          <a href="{{ URL::to('/')}}/optionImages/{{$optlist->optionImages}}" target="_blank">Option Image</a>
                                                          
                                                          <br><input type="button" name="removeProof" id="remove" value="Remove Image" onclick="removeAlphaFile({{$optlist->id}});" class="btn btn-primary" >
                                                        </div>
                                                        
                                                   </div>
                                                  @endif
                                                 
                                              </div>

                                              <div class="col-sm-2 col-md-2 col-lg-2">
                                                  <label>Sequence</label>
                                                  <br>  
                                                  <input type="text" style="width:80%;" name="wordSequenceAl[{{$optlist->id}}]" required="required" maxlength="30"   id="wordSequenceAl{{$k}}" class="form-control" placeholder="" pattern="[0-9]+" value="{{$optlist->wordSequence}}">
                                              </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Upload Pronunciation File</label>
                                              <br>  
                                              <input type="file" style="width:100%;" name="pronounciationFile[{{$optlist->id}}]"   id="pronounciationFile{{$k}}" class="form-control" placeholder="Pronunciation File">

                                              @if ($optlist->pronounciationFile!='')
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;">
                                                    <a href="{{ URL::to('/')}}/pronounciationFile/{{$optlist->pronounciationFile}}" target="_blank">Pronunciation File</a>
                                                    
                                                    <br><input type="button" name="removeProof" id="remove" value="Remove File" onclick="removePronounciationFile({{$optlist->id}});" class="btn btn-primary" >
                                                  </div>
                                                  
                                             </div>
                                             @endif

                                             </div>

                                               <div class="col-sm-1 col-md-1 col-lg-1" style="margin-top:25px;"><span onclick="return removeAl({{$k}},{{$optlist->id}});" class="delete_btn">Delete</span></div>
                                              
                                          </div>

                                    </div>  

                           
                                   @if ($k==count($pronounciationFile))
                                    </div>
                                   @endif    
                          
                              @endif

                              <?php $k++; ?>
                              @endforeach 

                              @if(count($pronounciationFile)<=1)
                              <div id="secondansweroptionslist6"></div>
                              @endif

                       @else

                          <label><span onclick="return addNewOptions6();" class="add_btn">Add Options</span></label>
                          <input type="hidden" name="optioncount6" id="optioncount6" value="1">
                          <div id="answeroptionslist6">
                             <div id="optionidal1">
                               <label>Option 1</label>  
                                    <div class="row">
                                      

                                       <div class="col-sm-2 col-md-2 col-lg-2">
                                        <label>Text</label>
                                        <br>  
                                        <input type="text"  name="optionNameAl[1]"   maxlength="100"   id="optionNameAl1" class="form-control" placeholder="" value="{{ old('optionNameAl.1', '') }}">
                                       </div>

                                       <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Image</label> 
                                        <br> 
                                        <input name="optionImages"  id="optionImages" placeholder="" class="form-control" style="width: 80%;" type="file">
                                      </div>

                                       <div class="col-sm-2 col-md-2 col-lg-2">
                                                  <label>Sequence</label>
                                                  <br>  
                                                  <input type="text" style="width:80%;" name="wordSequenceAl[1]" required="required" maxlength="30"   id="wordSequenceAl1" class="form-control" placeholder="" pattern="[0-9]+" value="">
                                      </div>

                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Upload Pronunciation File</label>
                                              <br>  
                                          <input type="file" style="width:80%;" name="pronounciationFile[1]"  id="pronounciationFile1" class="form-control" placeholder="Pronunciation File">
                                       </div>

                                       

                                    </div>
                              </div>     
                           </div>
                           <div id="secondansweroptionslist6">
                           </div>  
                        
                 @endif 
                   </div>  
                    <input type="submit" name="SubmitVal" value="Submit" class="btn btn-primary">
                    <div class="btn btn-primary" id="remove_all_option" >Remove Selected Correct Option</div>
                    <!-- <button class="btn btn-primary">Submit</button> -->
                </form>
            </div>
        </div>
      </div>
      </div>      
    </div>
@stop
