@extends('admin.layouts.layout-basic')

@section('scripts')
    <script src="/assets/admin/js/levelmanagement/validation.js"></script>
    <script type="text/javascript">
function selectall()
{
    
    if(document.form.delall.checked==true)
    {
        $(".uniquechk").prop('checked', true);
        /*alert ("adadd");
        exit;
        var chks = document.getElementsByName('del[]');
        
        for(i=0;i<chks.length;i++)
        {
            chks[i].checked=true;
        }*/
    }
    else if(document.form.delall.checked==false)
    {
        $(".uniquechk").prop('checked', false);
       /* alert ("adad2d");
        exit;
        var chks = document.getElementsByName('del[]');
        
        for(i=0;i<chks.length;i++)
        {
            chks[i].checked=false;
        }*/
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
            if(temp==false) {
                return false;
            }
            else{
                document.getElementById("delId").value="del";
                document.form.submit();
            }
    }
}
    </script>
@stop

@section('content')
    <div class="main-content">
        <div class="page-header center">
            <h3 class="page-title">Lesson Mapping for @if(isset($levelmanagement)) {{$levelmanagement->levelName}}  @endif Level</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{URL::to('admin/')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{URL::to('admin/levelmanagement/')}}">Level Management</a></li>
                <li class="breadcrumb-item active">Lesson Mapping for @if(isset($levelmanagement)) {{$levelmanagement->levelName}}  @endif Level</li>
            </ol>
        </div>
	 <div class="row">
       <div class="col-sm-12">	
        <div class="card center">
            
            <div class="card-body">
                <form id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($levelmanagement)){{ URL::to('admin/levelmanagement/assignlession/'.$levelmanagement->levelId.'')}}@endif" name="form" novalidate>
                    {{csrf_field()}}
					<input type="hidden" name="delId" id = "delId" value="" />
					@if(isset($levelmanagement))
		            <input type="hidden" name="edit_id" value="{{$levelmanagement->levelId}}"> 
	                @endif
					
                    
					 <div class="row">
                      <div class="col-sm-12">
                          
                          <div class="row">
                             <div class="col-sm-3 col-md-3 col-lg-3">
                                <p><input type="checkbox" name="delall" onClick="selectall();"> Select All</p>
                             </div>
                             <div class="col-sm-3 col-md-3 col-lg-3">
                                <p>Lesson Name</p>
                             </div>
                             <div class="col-sm-3 col-md-3 col-lg-3">
                                <p>Lesson Sequence</p>
                             </div>
                          </div>
                          
                          @if(isset($lessionmanagement))

                             @foreach($lessionmanagement as $vals)

                              <div class="row">
                                 
                                 <div class="col-sm-3 col-md-3 col-lg-3">
                                    <p><input type="checkbox" class='uniquechk' name='del[{{$vals->lessionId}}]' @if(isset($levelmanagement)){{(! empty(Laraspace\Http\Controllers\Controller::getLessionLevelValue($levelmanagement->levelId,$vals->lessionId))?'checked':'') }}@endif value="{{$vals->lessionId}}"></p>
                                 </div>
                                 
                                 <div class="col-sm-3 col-md-3 col-lg-3">
                                    <p>{{$vals->lessionName}}</p>
                                    <input type="hidden" name="lessionName[{{$vals->lessionId}}]" value="{{$vals->lessionName}}">
                                 </div>
                                 
                                 <div class="col-sm-3 col-md-3 col-lg-3">
                                    <p><input type="text" name="sortorder[{{$vals->lessionId}}]"  id="sortorder_{{$vals->lessionId}}" class="form-control" value="{{Laraspace\Http\Controllers\Controller::getLessionLevelsortOrder($levelmanagement->levelId,$vals->lessionId)}}" style="width:50%;"></p>
                                 </div>

                              </div>

                             @endforeach  


                          @endif      


                      </div>

                     </div>   

                    
                    <button class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
	  </div>
      </div>	  
    </div>
@stop
