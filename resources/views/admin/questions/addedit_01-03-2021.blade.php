@extends('admin.layouts.layout-basic')

@section('scripts')
   
    <style type="text/css">
      #answeroptions6,label {font-size:15px;}
    </style>
    <script type="text/javascript">
      @php if(!empty($questions) && $questions->questionType != 0){ @endphp
         $( document ).ready(function() {
            var questionType = @php echo $questions->questionType; @endphp ;
                          
                  if (questionType==1) {
                    $('#answeroptions').show();
                    //$('.type1').prop('required',true); 
                    $('.type2').prop('required',false); 
                    $('.type3').prop('required',false);
                    $('.type4').prop('required',false);
                    $('.type5').prop('required',false);
                    $('.type6').prop('required',false);
                    $('#question').prop('required',true);  
                }

                if (questionType==2) {
                    $('#answeroptions2').show();
                    $('.type1').prop('required',false);
                    //$('.type2').prop('required',true);
                    $('.type3').prop('required',false);
                    $('.type4').prop('required',false);
                    $('.type5').prop('required',false);
                    $('.type6').prop('required',false); 
                    $('#question').prop('required',true);  
                }

                if (questionType==3) {
                    $('#answeroptions3').show();
                    $('.type1').prop('required',false);
                    $('.type2').prop('required',false);
                   // $('.type3').prop('required',true);
                    $('.type4').prop('required',false);
                    $('.type5').prop('required',false);
                    $('.type6').prop('required',false);
                    $('#question').prop('required',true); 
                    $('#vocals').show();  
                }

                if (questionType==4) {

                    $('#answeroptions4').show();
                    $('.type1').prop('required',false);
                    $('.type2').prop('required',false);
                    $('.type3').prop('required',false);
                   // $('.type4').prop('required',true);
                    $('.type5').prop('required',false);
                    $('.type6').prop('required',false);
                    $('#question').prop('required',true); 
                      
                }

                if (questionType==5) {

                    $('#answeroptions5').show();
                    $('.type1').prop('required',false);
                    $('.type2').prop('required',false);
                    $('.type3').prop('required',false);
                    $('.type4').prop('required',false);
                   // $('.type5').prop('required',true);
                    $('.type6').prop('required',false);
                    $('#question').prop('required',false);
                    
                    $('#ques_name').hide();
                      
                }

                if (questionType==6) {

                    $('#answeroptions6').show();
                    $('.type1').prop('required',false);
                    $('.type2').prop('required',false);
                    $('.type3').prop('required',false);
                    $('.type4').prop('required',false);
                    $('.type5').prop('required',false);
                   // $('.type6').prop('required',true);
                    $('#question').prop('required',true); 
                      
                }

                if (questionType==7) {

                    //$('#answeroptions7').show();
                    $('#createvideos').show();
                    $('.type1').prop('required',false);
                    $('.type2').prop('required',false);
                    $('.type3').prop('required',false);
                    $('.type4').prop('required',false);
                    $('.type5').prop('required',false);
                    $('.type6').prop('required',false);
                   // $('.type6').prop('required',true);
                    $('#question').prop('required',true); 
                      
                }

                

         });
      @php } @endphp  
  
  $('#question').blur(function() {

    var question =$('#question').val();
    var questionType=$('#questionType').val();
    var que=question.trim();
    
    $('#quesexpl').html('');
    $('#fillword').html('');

    if (que!='' && questionType==3) {
        
        $('#wordoption2').show();

        var res = que.split(" ");
        
        for( var i = 0; i < res.length; i++ ) {
           $('#quesexpl').append('<div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>'+res[i]+'</label><input type="hidden" name="originaltext['+i+']" value="'+res[i]+'"></div><div class="col-sm-3 col-md-3 col-lg-3"><label><input type="checkbox" name="heightlight['+i+']" style="width: 70px;margin-top: 5px;" value="1"></label></div><div class="col-sm-5 col-md-5 col-lg-5"><label><input type="text" style="width:70%;line-height: 0.5;" name="translation['+i+']"  maxlength="5" id="translation1" class="form-control type3" placeholder=""  value=""></label></div></div>');

        }
    }

    if (que!='' && questionType==4) {

          $('#selectfillword').show();
          
          var res = que.split(" ");
          
          for( var i = 0; i < res.length; i++ ) {
             $('#fillword').append('<option value="'+res[i]+'">'+res[i]+'</option')
          }

    }

    

  });

 $('#questionType').change(function(){
    var questionType = $(this).val();  
   
    $('#answeroptions').hide();
    $('#answeroptions2').hide();
    $('#answeroptions3').hide();
    $('#answeroptions4').hide();
    $('#answeroptions5').hide();
    $('#answeroptions6').hide();
    $('#vocals').hide();
    $('#ques_name').show();
    $('#wordoption2').hide();
    $('#selectfillword').hide();
    $('#createvideos').hide();

    if (questionType==1) {
        $('#answeroptions').show();
        $('.type1').prop('required',true); 
        $('.type2').prop('required',false); 
        $('.type3').prop('required',false);
        $('.type4').prop('required',false);
        $('.type5').prop('required',false);
        $('.type6').prop('required',false);
        $('#question').prop('required',true);  
    }

    if (questionType==2) {
        $('#answeroptions2').show();
        $('.type1').prop('required',false);
        $('.type2').prop('required',true);
        $('.type3').prop('required',false);
        $('.type4').prop('required',false);
        $('.type5').prop('required',false);
        $('.type6').prop('required',false); 
        $('#question').prop('required',true);  
    }

    if (questionType==3) {
        $('#answeroptions3').show();
        $('.type1').prop('required',false);
        $('.type2').prop('required',false);
        $('.type3').prop('required',true);
        $('.type4').prop('required',false);
        $('.type5').prop('required',false);
        $('.type6').prop('required',false);
        $('#question').prop('required',true); 
        $('#vocals').show();
        $('#wordoption2').show();  
    }

    if (questionType==4) {

        $('#answeroptions4').show();
        $('.type1').prop('required',false);
        $('.type2').prop('required',false);
        $('.type3').prop('required',false);
        $('.type4').prop('required',true);
        $('.type5').prop('required',false);
        $('.type6').prop('required',false);
        $('#question').prop('required',true);
        $('#selectfillword').show(); 
          
    }

    if (questionType==5) {

        $('#answeroptions5').show();
        $('.type1').prop('required',false);
        $('.type2').prop('required',false);
        $('.type3').prop('required',false);
        $('.type4').prop('required',false);
        $('.type5').prop('required',true);
        $('.type6').prop('required',false);
        $('#question').prop('required',false);
        
        $('#ques_name').hide();
          
    }

    if (questionType==6) {

        $('#answeroptions6').show();
        $('.type1').prop('required',false);
        $('.type2').prop('required',false);
        $('.type3').prop('required',false);
        $('.type4').prop('required',false);
        $('.type5').prop('required',false);
        $('.type6').prop('required',true);
        $('#question').prop('required',true); 
          
    }

     if (questionType==7) {

          //$('#answeroptions7').show();
          $('#createvideos').show();
          $('.type1').prop('required',false);
          $('.type2').prop('required',false);
          $('.type3').prop('required',false);
          $('.type4').prop('required',false);
          $('.type5').prop('required',false);
          $('.type6').prop('required',false);
         // $('.type6').prop('required',true);
          $('#question').prop('required',true); 
                      
      }
    

  });


 /* For Option 6 */

  function addNewOptions6() {
       
       
     var optioncount=$('#optioncount6').val();
     optioncount=parseInt(optioncount) + 1;
   
   
       $('#secondansweroptionslist6').append('<div id="optionidal'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-2 col-md-2 col-lg-2"><label>Text</label><br><input type="text"  name="optionNameAl['+optioncount+']"  maxlength="30" id="optionNameAl'+optioncount+'" class="form-control" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br><input name="optionImages['+optioncount+']"  id="optionImages'+optioncount+'" placeholder="" class="form-control" style="width: 80%;" type="file"></div><div class="col-sm-2 col-md-2 col-lg-2"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceAl['+optioncount+']" required="required" maxlength="30"  id="wordSequenceAl1" class="form-control" placeholder="" attern="[0-9]+" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Upload Pronunciation File</label><br><input type="file" style="width:100%;" name="pronounciationFile['+optioncount+']" id="pronounciationFile'+optioncount+'" class="form-control"  placeholder="Pronunciation File"></div><div class="col-sm-1 col-md-1 col-lg-1" style="margin-top:25px;"><br><span onclick="return removeSelection6('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       $('#optioncount6').val(optioncount);
       
      
  }


  function addNewOptionsAl() {
       
       

       var rand= Math.floor((Math.random() * 10000) + 1);
       var optioncount=$('#optioncount6').val();
       optioncount=parseInt(optioncount) + 1;
       
       $('#secondansweroptionslist6').append('<div id="optionidal'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-2 col-md-2 col-lg-2"><label>Text</label><br><input type="text" name="optionNameAl['+rand+']"  maxlength="30" id="optionNameAl'+optioncount+'" class="form-control" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br><input name="optionImages['+rand+']"   id="optionImages'+optioncount+'" placeholder="" class="form-control" style="width: 80%;" type="file"></div><div class="col-sm-2 col-md-2 col-lg-2"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceAl['+rand+']" required="required" maxlength="30"  id="wordSequenceAl1" class="form-control" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Upload Pronunciation File</label><br><input type="file" style="width:100%;" name="pronounciationFile['+rand+']" id="pronounciationFile'+optioncount+'" class="form-control"  placeholder="Pronunciation File"></div><div class="col-sm-1 col-md-1 col-lg-1 style="margin-top:25px;""><br><span onclick="return removeAl('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       
       $('#optioncount6').val(optioncount);
      
  }

  function removeSelection6(idvals) {

    var optioncount=$('#optioncount6').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount6').val(optioncount);
    $('#optionidal'+idvals).remove();
    var optioncount=$('#optioncount6').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist6').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   
                   $('#secondansweroptionslist6').append('<div id="optionidal'+i+'"><br><label>Option '+i+'</label><div class="row"><div class="col-sm-2 col-md-2 col-lg-2"><label>Text</label><br><input type="text"  name="optionNameAl['+i+']" required="required" maxlength="30" id="optionNameAl'+i+'" class="form-control" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Image</label><br><input name="optionImages['+i+']"  id="optionImages'+i+'" placeholder="" class="form-control" style="width: 80%;" type="file"></div><div class="col-sm-2 col-md-2 col-lg-2"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceAl['+i+']" required="required" maxlength="30"  id="wordSequenceAl1" class="form-control" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Upload Pronunciation File</label><br><input type="file" style="width:100%;" name="pronounciationFile['+i+']" id="pronounciationFile'+i+'" class="form-control"  placeholder="Pronunciation File"></div><div class="col-sm-1 col-md-1 col-lg-1" style="margin-top:25px;"><br><span onclick="return removeSelection6('+i+');" class="delete_btn">Delete</span></div></div></div>');
                   
              }

         }

    }

  function removeAl(idvals,optionsId) {

      

      if (idvals!='' && confirm("Are you sure want to delete this option?")) {

          var optioncount=$('#optioncount6').val();
          optioncount=parseInt(optioncount) - 1;
          $('#optioncount6').val(optioncount);
          $('#optionidal'+idvals).remove();
          var optioncount=$('#optioncount6').val();

              $.ajax({

                  url: "{{URL::to('admin/')}}/questions/removeOptionAl/"+optionsId,
                  type: "get",
                  
                  success: function(html) {

                    if (html!='') {
                      
                      alert("You have successfully deleted option.");
                      window.location.reload();

                      }
                  }

              });
           
      }
      
  }


   /* For Option 5 */

  function addNewOptions5() {
       
       
     var optioncount=$('#optioncount5').val();
     optioncount=parseInt(optioncount) + 1;
   
   
       $('#secondansweroptionslist5').append('<div id="optionidmatch'+optioncount+'"><br><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+optioncount+'</label><br><input type="text" style="width:80%;" name="originalWord['+optioncount+']" required="required" maxlength="30" id="originalWord'+optioncount+'" class="form-control type5" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+optioncount+'</label><br><input type="text" style="width:80%;" name="translateWord['+optioncount+']" required="required" maxlength="50" id="translateWord'+optioncount+'" class="form-control type5" placeholder=""  value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection5('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       $('#optioncount5').val(optioncount);
       
      
  }


  function addNewOptionsMatch() {
       
       var rand= Math.floor((Math.random() * 10000) + 1);
       var optioncount=$('#optioncount5').val();
       optioncount=parseInt(optioncount) + 1;
       
       $('#secondansweroptionslist5').append('<div id="optionidmatch'+optioncount+'"><br><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+optioncount+'</label><br><input type="text" style="width:80%;" name="originalWord['+rand+']" required="required" maxlength="30" id="originalWord'+optioncount+'" class="form-control type5" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+optioncount+'</label><br><input type="text" style="width:80%;" name="translateWord['+rand+']" required="required" maxlength="50" id="translateWord'+optioncount+'" class="form-control type5" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelectionMatch('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       
       $('#optioncount5').val(optioncount);
      
  }

  function removeSelection5(idvals) {

    var optioncount=$('#optioncount5').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount5').val(optioncount);
    $('#optionidmatch'+idvals).remove();
    var optioncount=$('#optioncount5').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist5').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   
                   $('#secondansweroptionslist5').append('<div id="optionidmatch'+i+'"><br><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+i+'</label><br><input type="text" style="width:80%;" name="originalWord['+i+']" required="required" maxlength="30" id="originalWord'+i+'" class="form-control type5" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Word '+i+'</label><br><input type="text" style="width:80%;" name="translateWord['+i+']" required="required" maxlength="50" id="translateWord'+i+'" class="form-control type5" placeholder=""  value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection5('+i+');" class="delete_btn">Delete</span></div></div></div>');
                   
              }

         }

    }

function removeSelectionMatch(idvals,optionsId) {

    

    if (idvals!='' && confirm("Are you sure want to delete this word?")) {

        var optioncount=$('#optioncount5').val();
        optioncount=parseInt(optioncount) - 1;
        $('#optioncount5').val(optioncount);
        $('#optionidmatch'+idvals).remove();
        var optioncount=$('#optioncount5').val();

            $.ajax({

                url: "{{URL::to('admin/')}}/questions/removeOptionWordMatch/"+optionsId,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted word.");
                    window.location.reload();

                    }
                }

            });
         
    }
    
}

   
   /* For Option 4 */

  function addNewOptions4() {
       
       
     var optioncount=$('#optioncount4').val();
     optioncount=parseInt(optioncount) + 1;
   
   
       $('#secondansweroptionslist4').append('<div id="optionidfillblank'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="optionName['+optioncount+']" required="required" maxlength="30" id="optionName'+optioncount+'" class="form-control type4" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection4('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       $('#optioncount4').val(optioncount);
       
      
  }


  function addNewOptionsFillBlank() {
       
       var rand= Math.floor((Math.random() * 10000) + 1);
       var optioncount=$('#optioncount4').val();
       optioncount=parseInt(optioncount) + 1;
       
       $('#secondansweroptionslist4').append('<div id="optionidfillblank'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="optionName['+rand+']" required="required" maxlength="30" id="optionName'+optioncount+'" class="form-control type4" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelectionword('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       
       $('#optioncount4').val(optioncount);
      
  }

  function removeSelection4(idvals) {

    var optioncount=$('#optioncount4').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount4').val(optioncount);
    $('#optionidfillblank'+idvals).remove();
    var optioncount=$('#optioncount4').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist4').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   
                   $('#secondansweroptionslist4').append('<div id="optionidfillblank'+i+'"><br><label>Option '+i+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="optionName['+i+']" required="required" maxlength="30" id="optionName'+i+'" class="form-control type4" placeholder="" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection4('+i+');" class="delete_btn">Delete</span></div></div></div>');
                   
              }

         }

    }

  function removeFillBlankWord(idvals,optionsId) {

      

      if (idvals!='' && confirm("Are you sure want to delete this option?")) {

          var optioncount=$('#optioncount4').val();
          optioncount=parseInt(optioncount) - 1;
          $('#optioncount4').val(optioncount);
          $('#optionidfillblank'+idvals).remove();
          var optioncount=$('#optioncount4').val();

              $.ajax({

                  url: "{{URL::to('admin/')}}/questions/removeOptionFillBlank/"+optionsId,
                  type: "get",
                  
                  success: function(html) {

                    if (html!='') {
                      
                      alert("You have successfully deleted option.");
                      window.location.reload();

                      }
                  }

              });
           
      }
      
  }

 
   /* For Option 3 */

  function addNewOptions3() {
       
       
     var optioncount=$('#optioncount3').val();
     optioncount=parseInt(optioncount) + 1;
   
   
       $('#secondansweroptionslist3').append('<div id="optionidwordtwo'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-6 col-md-6 col-lg-6"><label>Text</label><br><input type="text" style="width:80%;" name="wordTextTwo['+optioncount+']" required="required" maxlength="30" id="wordTextTwo'+optioncount+'" class="form-control type3" placeholder="" value=""></div><div class="col-sm-4 col-md-4 col-lg-4"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceTwo['+optioncount+']" required="required" maxlength="5" id="wordSequenceTwo'+optioncount+'" class="form-control type3" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-2 col-md-2 col-lg-2"><br><span onclick="return removeSelection3('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       $('#optioncount3').val(optioncount);
       
      
  }


  function addNewOptionsTwoWord() {
       
       var rand= Math.floor((Math.random() * 10000) + 1);
       var optioncount=$('#optioncount3').val();
       optioncount=parseInt(optioncount) + 1;
       
       $('#secondansweroptionslist3').append('<div id="optionidwordtwo'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-6 col-md-6 col-lg-6"><label>Text</label><br><input type="text" style="width:80%;" name="wordTextTwo['+rand+']" required="required" maxlength="30" id="wordTextTwo'+optioncount+'" class="form-control type3" placeholder="" value=""></div><div class="col-sm-4 col-md-4 col-lg-4"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceTwo['+rand+']" required="required" maxlength="5" id="wordSequenceTwo'+optioncount+'" class="form-control type3" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-2 col-md-2 col-lg-2"><br><span onclick="return removeSelectionwordTwo('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       
       $('#optioncount3').val(optioncount);
      
  }

  function removeSelection3(idvals) {

    var optioncount=$('#optioncount3').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount3').val(optioncount);
    $('#optionidwordtwo'+idvals).remove();
    var optioncount=$('#optioncount3').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist3').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   
                   $('#secondansweroptionslist3').append('<div id="optionidwordtwo'+i+'"><br><label>Option '+i+'</label><div class="row"><div class="col-sm-6 col-md-6 col-lg-6"><label>Text</label><br><input type="text" style="width:80%;" name="wordTextTwo['+i+']" required="required" maxlength="30" id="wordTextTwo'+i+'" class="form-control type3" placeholder="" value=""></div><div class="col-sm-4 col-md-4 col-lg-4"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequenceTwo['+i+']" required="required" maxlength="5" id="wordSequenceTwo'+i+'" class="form-control type3" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-2 col-md-2 col-lg-2"><br><span onclick="return removeSelection3('+i+');" class="delete_btn">Delete</span></div></div></div>');
                   
              }

         }

    }

function removeSelectionwordTwo(idvals,optionsId) {

    

    if (idvals!='' && confirm("Are you sure want to delete this option?")) {

        var optioncount=$('#optioncount3').val();
        optioncount=parseInt(optioncount) - 1;
        $('#optioncount3').val(optioncount);
        $('#optionidwordtwo'+idvals).remove();
        var optioncount=$('#optioncount3').val();

            $.ajax({

                url: "{{URL::to('admin/')}}/questions/removeOptionWordTwo/"+optionsId,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted option.");
                    window.location.reload();

                    }
                }

            });
         
    }
    
}

    /* For Option 2 */

  function addNewOptions2() {
       
       
     var optioncount=$('#optioncount2').val();
     optioncount=parseInt(optioncount) + 1;
   
   
       $('#secondansweroptionslist2').append('<div id="optionidword'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="wordText['+optioncount+']" required="required" maxlength="30" id="wordText'+optioncount+'" class="form-control type2" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequence['+optioncount+']" required="required" maxlength="5" id="wordSequence'+optioncount+'" class="form-control type2" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection2('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       $('#optioncount2').val(optioncount);
       
      
  }


  function addNewOptionsOneWord() {
       
       var rand= Math.floor((Math.random() * 10000) + 1);
       var optioncount=$('#optioncount2').val();
       optioncount=parseInt(optioncount) + 1;
       
       $('#secondansweroptionslist2').append('<div id="optionidword'+optioncount+'"><br><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="wordText['+rand+']" required="required" maxlength="30" id="wordText'+optioncount+'" class="form-control type2" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequence['+rand+']" required="required" maxlength="5" id="wordSequence'+optioncount+'" class="form-control type2" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelectionword('+optioncount+');" class="delete_btn">Delete</span></div></div></div>');
       
       $('#optioncount2').val(optioncount);
      
  }

  function removeSelection2(idvals) {

    var optioncount=$('#optioncount2').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount2').val(optioncount);
    $('#optionidword'+idvals).remove();
    var optioncount=$('#optioncount2').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist2').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   
                   $('#secondansweroptionslist2').append('<br><div id="optionidword'+i+'"><br><label>Option '+i+'</label><div class="row"><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br><input type="text" style="width:80%;" name="wordText['+i+']" required="required" maxlength="30" id="wordText'+i+'" class="form-control type2" placeholder="" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Sequence</label><br><input type="text" style="width:80%;" name="wordSequence['+i+']" required="required" maxlength="5" id="wordSequence'+i+'" class="form-control type2" placeholder="" pattern="[0-9]+" value=""></div><div class="col-sm-1 col-md-1 col-lg-1"><br><span onclick="return removeSelection2('+i+');" class="delete_btn">Delete</span></div></div></div>');
                   
              }

         }

    }

function removeSelectionword(idvals,optionsId) {

    

    if (idvals!='' && confirm("Are you sure want to delete this option?")) {

        var optioncount=$('#optioncount2').val();
        optioncount=parseInt(optioncount) - 1;
        $('#optioncount2').val(optioncount);
        $('#optionidword'+idvals).remove();
        var optioncount=$('#optioncount2').val();

            $.ajax({

                url: "{{URL::to('admin/')}}/questions/removeOptionWord/"+optionsId,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted option.");
                    window.location.reload();

                    }
                }

            });
         
    }
    
}

   /* For Option 1 */

  function addNewOptions() {
       
       var optioncount=$('#optioncount').val();
       optioncount=parseInt(optioncount) + 1;
   

       $('#secondansweroptionslist').append('<div  id="optionid'+optioncount+'"><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-5 col-md-5 col-lg-5"><label>Browse Image</label><br><input type="file" style="width:100%;"  name="questionImage['+optioncount+']"   id="questionImage'+optioncount+'" class="form-control" placeholder="Image '+optioncount+'"></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br>  <input type="text" style="width:80%;" name="questionImageText['+optioncount+']"  maxlength="30"  id="text'+optioncount+'" class="form-control" placeholder="Melon" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Is Correct Answer</label><br><input type="radio" style="width:40%;" name="isCorrectAnswer['+optioncount+']"    id="isCorrectAnswer'+optioncount+'" class="form-control radioinstant" onclick="return checkRadio('+optioncount+');" value="1"></div><span onclick="return removeSelection('+optioncount+');" class="delete_btn">Delete</span></div></div>');
       $('#optioncount').val(optioncount);
      
  }


  function addNewOptions1() {
       
       var rand= Math.floor((Math.random() * 10000) + 1);
       

      var optioncount=$('#optioncount').val();
      optioncount=parseInt(optioncount) + 1;
   
  

       $('#secondansweroptionslist').append('<div  id="optionid'+optioncount+'"><label>Option '+optioncount+'</label><div class="row"><div class="col-sm-5 col-md-5 col-lg-5"><label>Browse Image</label><br><input type="file" style="width:100%;"  name="questionImage['+rand+']"   id="questionImage'+optioncount+'" class="form-control" placeholder="Image '+optioncount+'"></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br>  <input type="text" style="width:80%;" name="questionImageText['+rand+']"  maxlength="30"  id="text'+optioncount+'" class="form-control" placeholder="Melon" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Is Correct Answer</label><br><input type="radio" style="width:40%;" name="isCorrectAnswer['+rand+']"    id="isCorrectAnswer'+optioncount+'" class="form-control radioinstant" onclick="return checkRadio('+optioncount+');" value="1"></div><span onclick="return removeSelection1('+optioncount+');" class="delete_btn">Delete</span></div></div>');
       $('#optioncount').val(optioncount);
      
  }


function removeSelection(idvals) {

    var optioncount=$('#optioncount').val();
    optioncount=parseInt(optioncount) - 1;
    $('#optioncount').val(optioncount);
    $('#optionid'+idvals).remove();
    var optioncount=$('#optioncount').val();
     //alert(optioncount);
    if (optioncount >1) {

         $('#secondansweroptionslist').html('');
      

               for (var i=2;i<=optioncount;i++) {
                 
                   $('#secondansweroptionslist').append('<div  id="optionid'+i+'"><label>Option '+i+'</label><div class="row"><div class="col-sm-5 col-md-5 col-lg-5"><label>Browse Image</label><br><input type="file" style="width:100%;" name="questionImage['+i+']"    id="questionImage'+i+'" class="form-control" placeholder="Image '+i+'"></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Text</label><br>  <input type="text" style="width:80%;" name="questionImageText['+i+']"  maxlength="30"   id="text'+i+'" class="form-control" placeholder="Melon" value=""></div><div class="col-sm-3 col-md-3 col-lg-3"><label>Is Correct Answer</label><br><input type="radio" style="width:40%;" name="isCorrectAnswer['+i+']"    id="isCorrectAnswer'+i+'" class="form-control radioinstant" onclick="return checkRadio('+i+');" value="1"></div><span onclick="return removeSelection('+i+');" class="delete_btn">Delete</span></div></div>');
                   
              }

         }

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

function removeImage(Id) {
    

    if (Id!='' && confirm("Are you sure want to delete this picture?")) {

         $.ajax({

                url: "{{URL::to('admin/')}}/questions/deleteImages/"+Id,
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

function removeFile(Id) {
    

    if (Id!='' && confirm("Are you sure want to delete this Vocal File?")) {

         $.ajax({

                url: "{{URL::to('admin/')}}/questions/deleteVocal/"+Id,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted Vocal File.");
                    window.location.reload();

                    }
                }

            });

    }

}

function removeVideoFile(Id) {
    

    if (Id!='' && confirm("Are you sure want to delete this Question Video?")) {

         $.ajax({

                url: "{{URL::to('admin/')}}/questions/deleteVideo/"+Id,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted Question Video.");
                    window.location.reload();

                    }
                }

            });

    }

}

function removePronounciationFile(Id) {
    

    if (Id!='' && confirm("Are you sure want to delete this Pronunciation File?")) {

         $.ajax({

                url: "{{URL::to('admin/')}}/questions/deletePronounciationFile/"+Id,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted Pronunciation File.");
                    window.location.reload();

                    }
                }

            });

    }

}

function removeAlphaFile(Id) {
    

    if (Id!='' && confirm("Are you sure want to delete this Image Option File?")) {

         $.ajax({

                url: "{{URL::to('admin/')}}/questions/removeQuestionImageFile/"+Id,
                type: "get",
                
                success: function(html) {

                  if (html!='') {
                    
                    alert("You have successfully deleted Image Option File.");
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
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($questions)){{ URL::to('admin/questions/edit/'.$questions->questionId.'')}}@else{{ URL::to('admin/questions/add') }}@endif" name="questions" novalidate>
                    {{csrf_field()}}
					
					@if(isset($questions))
		            <input type="hidden" name="edit_id" value="{{$questions->questionId}}"> 
	                @endif
					
                    <div class="form-group">
                        <label>Select Lesson</label>
                        <select name="lessionId" required="required" class="form-control ls-select2">
                            <option value="">Select Lesson</option>
                            @if($lessionmanagement)
                               @foreach($lessionmanagement as $vals)
                                <option value="{{$vals->lessionId}}" @if(isset($questions) && $questions->lessionId==$vals->lessionId) selected @endif>{{$vals->lessionName}}</option>
                               @endforeach

                            @endif
                           
                            
                        </select>
                    </div>

                     <div class="form-group" >
                        <label>Question Type<span class="req">*</span></label>
                        <select name="questionType" id="questionType"  @if(isset($questions)) disabled="disabled"  required="required" @endif class="form-control ls-select2">
                            <option value="">Select Question Type</option>
                            <option value="1" @if(isset($questions) && $questions->questionType==1) selected @endif>Image Selection</option>
                            <option value="2" @if(isset($questions) && $questions->questionType==2) selected @endif>Create Sentence by selecting word 1</option>
                            <option value="3" @if(isset($questions) && $questions->questionType==3) selected @endif>Create Sentence by selecting word 2</option>
                            <option value="4" @if(isset($questions) && $questions->questionType==4) selected @endif>Fill in the blank</option>
                            <option value="5" @if(isset($questions) && $questions->questionType==5) selected @endif>Match the Following</option>
                            <option value="6" @if(isset($questions) && $questions->questionType==6) selected @endif>Alphabets & Numbers Learning</option> 
                            <option value="7" @if(isset($questions) && $questions->questionType==7) selected @endif>Create Video Question</option> 
                           
                            
                        </select>
                        @if(isset($questions))
                        <input type="hidden" name="questionType" id="questionType" value="{{$questions->questionType}}">
                        @endif
                    </div>

					          <div class="form-group" id="ques_name" @if((isset($questions) && $questions->questionType!=5) || !isset($questions))  @else style="display:none;"  @endif>
                        <label>Question Name<span class="req">*</span></label>
                        <input type="text" maxlength="100" @if((isset($questions) && $questions->questionType!=5))  @else required="required" @endif class="form-control" value="@if(isset($questions)){{$questions->question}}@endif" name="question" id="question">
                    </div>

                    <div class="form-group" id="vocals"  @if((isset($questions) && $questions->questionType!=3) || !isset($questions)) style="display:none;" @endif>
                         <label>Upload Vocals</label>
                            <input type="file" style="width:60%;" name="uploadVocals"  id="uploadVocals" class="form-control" placeholder="Vocal.mp3">
                            @if (isset($questions) && $questions->uploadVocals!='')
                             
                             <div class="row">
                                  <div style="margin-left:25px;margin-bottom:10px;margin-top:10px;"> 
                                    <a href="{{ URL::to('/')}}/questionVocals/{{$questions->uploadVocals}}" target="_blank">Vocal File</a>
                                    
                                    <br><input type="button" name="removeProof" style="margin-top:5px;" id="remove" value="Remove Vocals" onclick="removeFile({{$questions->questionId}});" class="btn btn-primary" >
                                  </div>
                                  
                             </div>
                             @endif
                    </div>  

                    <div class="form-group" id="createvideos"  @if((isset($questions) && $questions->questionType!=7) || !isset($questions)) style="display:none;" @endif>
                         <label>Upload Video</label>
                            <input type="file" style="width:60%;" name="video"  id="video" class="form-control" placeholder="sample.mp4">
                            @if (isset($questions) && $questions->video!='')
                             
                             <div class="row">
                                  <div style="margin-left:25px;margin-bottom:10px;margin-top:10px;"> 
                                    <a href="{{ URL::to('/')}}/questionVideos/{{$questions->video}}" target="_blank">Video File</a>
                                    
                                    <br><input type="button" name="removeVideo" style="margin-top:5px;" id="remove" value="Remove Video" onclick="removeVideoFile({{$questions->questionId}});" class="btn btn-primary" >
                                  </div>
                                  
                             </div>
                             @endif

                             <span><strong>Note:</strong> Preferable file format (.MP4,.MOV,.WMV,.AVI,.WEBM) </span>
                    </div>        

                   
                    <div class="form-group">
                        <label>Display Rank</label>
                         <input type="text" name="sortOrder" style="width:20%;" required="required" maxlength="5" tabindex="3"  id="sortOrder" class="form-control min_width1" placeholder="Rank" value="@if(isset($questions)){{$questions->sortOrder}}@else{{$rank}}@endif{{ old('rank') }}">
                     </div>

					           <div class="form-group">
                        <label>Status</label>
            						<select name="status" class="form-control ls-select2">
            							<option value="1" @if(isset($questions) && $questions->isActive==1) selected @endif>Active</option>
            							<option value="0" @if(isset($questions) && $questions->isActive==0) selected @endif>Inactive</option>
            						</select>
                     </div>

                     <div class="form-group" id="selectfillword" @if((isset($questions) && $questions->questionType!=4) || !isset($questions)) style="display:none;" @endif>
                           <label>Select word to be fill in as blank</label>
                           <br>
                           <select name="fillBlankWord" id="fillword" style="width:40% !important;" class="form-control">
                             @if(isset($questions) && $questions->questionType==4)
                             @foreach(explode(" ",$questions->question) as $value)
                             <option value="{{$value}}" @if($questions->fillBlankWord==$value) selected="selected" @endif>{{$value}}</option>
                             @endforeach
                             @else
                             <option value="">Select Word</option>
                             @endif
                           
                           </select> 

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
                                             
                                             <div class="col-sm-5 col-md-5 col-lg-5">
                                              <label>Browse Image</label>
                                              <br>  
                                              <input type="file" style="width:100%;" name="questionImage[{{$optlist->id}}]"   id="questionImage1" class="form-control" placeholder="Image 1">

                                              @if ($optlist->questionImage!='')
                                             <br>
                                             <div class="row">
                                                  <div style="margin-left:25px;margin-bottom:10px;"><img src="{{ URL::to('/')}}/questionImage/{{$optlist->questionImage}}" width="100px" height="100px" style="margin-bottom:10px;"> 
                                                    
                                                    <br><input type="button" name="removeProof" id="remove" value="Remove Image" onclick="removeImage({{$optlist->id}});" class="btn btn-primary" >
                                                  </div>
                                                  
                                             </div>
                                             @endif

                                             </div>


                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="questionImageText[{{$optlist->id}}]"  maxlength="30"   id="text1" class="form-control" placeholder="Melon" value="{{$optlist->questionImageText}}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Is Correct Answer</label>
                                              <br>  
                                              <input type="radio" style="width:40%;" name="isCorrectAnswer[{{$optlist->id}}]"    id="isCorrectAnswer1" class="form-control radioinstant" onclick="return checkRadio(1);" @if($optlist->isCorrectAnswer==1) checked="checked" @endif value="1">
                                             </div>
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
                                             
                                             <div class="col-sm-5 col-md-5 col-lg-5">
                                              <label>Browse Image</label>
                                              <br>  
                                              <input type="file" style="width:100%;" name="questionImage[{{$optlist->id}}]" @if ($optlist->questionImage=='') required="required" @endif  id="questionImage{{$k}}" class="form-control type1" placeholder="Image {{$k}}">

                                               @if ($optlist->questionImage!='')
                                               <br>
                                                 <div class="row">
                                                      <div style="margin-left:25px;margin-bottom:10px;"><img src="{{ URL::to('/')}}/questionImage/{{$optlist->questionImage}}" width="100px" height="100px" style="margin-bottom:10px;"> 
                                                       
                                                        <br><input type="button" name="removeProof" id="remove" value="Remove Image" onclick="removeImage({{$optlist->id}});" class="btn btn-primary" >
                                                      </div>
                                                      
                                                 </div>
                                                @endif

                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Text</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="questionImageText[{{$optlist->id}}]"  maxlength="30"  id="text{{$k}}" class="form-control" placeholder="Melon" value="{{$optlist->questionImageText}}">
                                             </div>

                                              <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Is Correct Answer</label>
                                              <br>  
                                              <input type="radio" style="width:40%;" name="isCorrectAnswer[{{$optlist->id}}]"  @if($optlist->isCorrectAnswer==1) checked="checked" @endif  id="isCorrectAnswer{{$k}}" class="form-control radioinstant" onclick="return checkRadio({{$k}});" @if($optlist->isCorrectAnswer==1) checked="checked" @endif value="1">
                                             </div>
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
                                       
                                       <div class="col-sm-5 col-md-5 col-lg-5">
                                        <label>Browse Image</label>
                                        <br>  
                                        <input type="file" style="width:100%;" name="questionImage[1]" id="questionImage1" class="form-control" placeholder="Image 1">
                                       </div>
                                      

                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Text</label>
                                        <br>  
                                        <input type="text" style="width:80%;" name="questionImageText[1]" @if(!isset($questions))  @endif maxlength="30"   id="text1" class="form-control" placeholder="Melon" value="">
                                       </div>

                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Is Correct Answer</label>
                                        <br>  
                                        <input type="radio" style="width:40%;" name="isCorrectAnswer[1]"  checked="checked"  id="isCorrectAnswer1" class="form-control radioinstant" onclick="return checkRadio(1);" value="1">
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
                                              <input type="text" style="width:80%;" name="wordText[{{$optlist->id}}]" required="required" maxlength="30"   id="wordText1" class="form-control type2" placeholder="" value="{{$optlist->wordText}}">
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
                                              <input type="text" style="width:80%;" name="wordText[{{$optlist->id}}]" required="required" maxlength="30"   id="wordText{{$k}}" class="form-control type2" placeholder="Melon" value="{{$optlist->wordText}}">
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
                                        <input type="text" style="width:80%;" name="wordText[1]" @if(!isset($questions)) required="required" @endif maxlength="30" id="wordText1" class="form-control type2" placeholder="" value="">
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
                                                  <input type="text" style="width:80%;" name="wordTextTwo[{{$optlist->id}}]" required="required" maxlength="30"   id="wordTextTwo1" class="form-control type3" placeholder="" value="{{$optlist->wordText}}">
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
                                                  <input type="text" style="width:80%;" name="wordTextTwo[{{$optlist->id}}]" required="required" maxlength="30"   id="wordTextTwo{{$k}}" class="form-control type3" placeholder="Melon" value="{{$optlist->wordText}}">
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
                                        
                                        <div class="col-sm-5 col-md-5 col-lg-5"><label><input type="text" style="width:70%;line-height: 0.5;" name="translation[{{$rows->id}}]"  maxlength="5" id="translation{{$i}}" class="form-control type3" placeholder=""  value="{{$rows->translatedText}}"></label>
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
                                            <input type="text" style="width:80%;" name="wordTextTwo[1]" @if(!isset($questions)) required="required" @endif maxlength="30" id="wordTextTwo1" class="form-control type3" placeholder="" value="">
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
                                              <input type="text" style="width:80%;" name="optionName[{{$optlist->id}}]"  maxlength="30"   id="wordText1" class="form-control type4" placeholder="" value="{{$optlist->optionName}}">
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
                                              <input type="text" style="width:80%;" name="optionName[{{$optlist->id}}]" required="required" maxlength="30"   id="optionName{{$k}}" class="form-control type4" placeholder="Melon" value="{{$optlist->optionName}}">
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
                                        <input type="text" style="width:80%;" name="optionName[1]" @if(!isset($questions)) required="required" @endif maxlength="30" id="optionName1" class="form-control type4" placeholder="" value="">
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
                                              <input type="text" style="width:80%;" name="originalWord[{{$optlist->id}}]" required="required" maxlength="30"   id="originalWord1" class="form-control type5" placeholder="" value="{{$optlist->originalWord}}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word 1</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="translateWord[{{$optlist->id}}]" required="required" maxlength="30"   id="translateWord{{$k}}" class="form-control type5" placeholder="1"  value="{{$optlist->translateWord}}">
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
                                              <input type="text" style="width:80%;" name="originalWord[{{$optlist->id}}]" required="required" maxlength="30"   id="originalWord{{$k}}" class="form-control type5" placeholder="" value="{{$optlist->originalWord}}">
                                             </div>

                                             <div class="col-sm-3 col-md-3 col-lg-3">
                                              <label>Word {{$k}}</label>
                                              <br>  
                                              <input type="text" style="width:80%;" name="translateWord[{{$optlist->id}}]" required="required" maxlength="30"   id="translateWord{{$k}}" class="form-control type5" placeholder=""  value="{{$optlist->translateWord}}">
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
                                        <input type="text" style="width:80%;" name="originalWord[1]" @if(!isset($questions)) required="required" @endif maxlength="30" id="originalWord1" class="form-control type5" placeholder="Water" value="">
                                       </div>
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Word 1</label>
                                        <br>  
                                        <input type="text" style="width:80%;" name="translateWord[1]" @if(!isset($questions)) required="required" @endif maxlength="5" id="translateWord1" class="form-control type5" placeholder="dlo"  value="">
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
                                              <input type="text"  name="optionNameAl[{{$optlist->id}}]"  maxlength="30"   id="optionNameAl1" class="form-control" placeholder="" value="{{$optlist->optionName}}">
                                             </div>

                                              <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                              <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Image</label> 
                                                <br> 
                                                <input name="optionImages[{{$optlist->id}}]"  id="optionImages{{$optlist->id}}" placeholder="" class="form-control" style="width: 80%;" type="file">
                                                 
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
                                              <input type="text"  name="optionNameAl[{{$optlist->id}}]"  maxlength="30"   id="optionNameAl{{$k}}" class="form-control" placeholder="" value="{{$optlist->optionName}}">
                                             </div>
                                              
                                              <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                               <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>Image</label> 
                                                <br> 
                                                <input name="optionImages[{{$optlist->id}}]"  id="optionImages{{$optlist->id}}" placeholder="" class="form-control" style="width: 80%;" type="file">
                                                 
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
                                        <input type="text"  name="optionNameAl[1]"   maxlength="30"   id="optionNameAl1" class="form-control" placeholder="" value="">
                                       </div>

                                       <div class="col-sm-1 col-md-1 col-lg-1"><span>(OR)</span></div>
                                       
                                       <div class="col-sm-3 col-md-3 col-lg-3">
                                        <label>Image</label> 
                                        <br> 
                                        <input name="optionImages[1]"  id="optionImages1" placeholder="" class="form-control" style="width: 80%;" type="file">
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
                    <!-- <button class="btn btn-primary">Submit</button> -->
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
