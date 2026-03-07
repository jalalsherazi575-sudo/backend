@extends('layouts.template')



@section('content')
    <div class="product-status mg-tb-15">
       

        


       <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="review-tab-pro-inner">

                             <ul id="myTab3" class="tab-review-design">
                                    <li class="active"><a href="#description">@if(isset($users)) View @else Add @endif User Role</a></li>
                                    <!-- <li><a href="#reviews"><i class="fa fa-file-image-o" aria-hidden="true"></i> Pictures</a></li> -->
                                    <!-- <li><a href="#INFORMATION"><i class="fa fa-commenting" aria-hidden="true"></i> Review</a></li> -->
                                </ul>

                                <div id="myTabContent" class="tab-content custom-product-edit">
                                    <div class="product-tab-list tab-pane fade active in" id="description">

                            <form class="form-horizontal" id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($users)){{ URL::to('admin/usersroles/edit/'.$users->role_id.'')}}@else{{ URL::to('admin/usersroles/create') }}@endif" name="user">
                                @csrf
                                @if(isset($users))
                               <input type="hidden" name="edit_id" value="{{$users->role_id}}"> 
                               @endif
                                <div class="row">
                                    
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        <div class="review-content-section">
                                        <label for="fname" class="col-sm-3">Role Name</label>
                                        <div class="col-sm-6">
                                            <input type="text" readonly="readonly" required="required" name="role_name" class="form-control" id="role_name" placeholder="Please Enter Role Name" value="@if(isset($users)) {{$users->role_name}} @endif">
                                        </div>
                                      </div>
                                    </div>
                                    
                                   
                                    
                                </div>
                                 
                                 <div class="card-body role_add_edit">
                                    <h5>Permision</h5>
                                    <br>
                                 
                                 
                                 <table class="table">
                                  <thead>
                                    <tr>
                                     
                                      <th scope="col">Module Name</th>
                                      <th scope="col">Create</th>
                                      <th scope="col">Update</th>
                                      <th scope="col">Delete</th>
                                      <th scope="col">View</th>
                                    </tr>
                                  </thead>
                                  
                                  <tbody>
                                    
                                    
                                    @if ($master_module_list)
                                      
                                      @foreach ($master_module_list as $lists)
                                    <tr>
                                      <td>{{$lists->module_name}}</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getCreateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="master_create" name="create[{{$lists->module_id}}]" id="master_create{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" class="master_update" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getUpdateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="update[{{$lists->module_id}}]" id="master_update{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" class="master_delete" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getDeleteModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="delete[{{$lists->module_id}}]" id="master_delete{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" class="master_view" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getViewModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="view[{{$lists->module_id}}]" id="master_view{{$lists->module_id}}" value="1"></td>
                                    </tr>
                                       @endforeach    
                                       
                                        @endif  
                                    
                                    
                                    @if ($user_module)
                                    @foreach ($user_module as $lists)
                                    <tr>
                                      <td>@if($lists->parent_id==0) <strong>{{$lists->module_name}}</strong> @else {{$lists->module_name}} @endif</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" name="user_create" id="user_create" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" name="user_update" id="user_update" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" name="user_delete" id="user_delete" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" name="user_view" id="user_view" value="1"></td>
                                    </tr>
                                    @endforeach 
                                     @endif
                                    
                                    @if ($user_module_list)
                                      
                                      @foreach ($user_module_list as $lists)
                                    <tr>
                                      <td>{{$lists->module_name}}</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getCreateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_create" name="create[{{$lists->module_id}}]" id="user_sub_create{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled"  @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getUpdateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_update" name="update[{{$lists->module_id}}]" id="user_sub_update{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getDeleteModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_delete" name="delete[{{$lists->module_id}}]" id="user_sub_delete{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" disabled="disabled" @if(isset($users)){{(! empty(App\Http\Controllers\Controller::getViewModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_view" name="view[{{$lists->module_id}}]" id="user_sub_view{{$lists->module_id}}" value="1"></td>
                                    </tr>
                                       @endforeach    
                                       
                                        @endif  
                                   
                                    



                                  </tbody>
                                  
                            </table>   
                            </div>
                          </div>
                                
                            </form>
                          </div>
                        </div>
                        </div>
                        
                        

                    </div>
                    
                </div>
       </div>

     
    </div>
@stop
@section('scripts')
  <script>
jQuery(document).ready(function($) {
//var dateToday = new Date();

$('.master_create').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal[IdVal.length -1];
   //alert(IdVal.match(/\d+/));
     var IDS=IdVal.match(/\d+/);
   if(document.getElementById('master_view'+IDS).checked == false && document.getElementById('master_create'+IDS).checked == true) {
            $('#master_view'+IDS).prop('checked', true);
         }
});

$('.master_update').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal[IdVal.length -1];
   var IDS=IdVal.match(/\d+/);
   if(document.getElementById('master_view'+IDS).checked == false && document.getElementById('master_update'+IDS).checked == true) {
            $('#master_view'+IDS).prop('checked', true);
         }
});

$('.master_delete').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal[IdVal.length -1];
   var IDS=IdVal.match(/\d+/);
   if(document.getElementById('master_view'+IDS).checked == false && document.getElementById('master_delete'+IDS).checked == true) {
            $('#master_view'+IDS).prop('checked', true);
         }
});

$('.user_create').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal.substr(IdVal.length - 1);
   var IDS=IdVal.match(/\d+/);
   if(document.getElementById('user_sub_view'+IDS).checked == false && document.getElementById('user_sub_create'+IDS).checked == true) {
            $('#user_sub_view'+IDS).prop('checked', true);
         }
});

$('.user_update').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal.substr(IdVal.length - 1);
   var IDS=IdVal.match(/\d+/);
   if(document.getElementById('user_sub_view'+IDS).checked == false && document.getElementById('user_sub_update'+IDS).checked == true) {
            $('#user_sub_view'+IDS).prop('checked', true);
         }
});

$('.user_delete').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal.substr(IdVal.length - 2);
   var IDS=IdVal.match(/\d+/);
   if(document.getElementById('user_sub_view'+IDS).checked == false && document.getElementById('user_sub_delete'+IDS).checked == true) {
            $('#user_sub_view'+IDS).prop('checked', true);
         }
});

$('.company_create').on( "click", function() {
   var IdVal=($(this).attr('id'));
  var lastChar = IdVal.substr(IdVal.length - 2);
   if(document.getElementById('company_sub_view'+lastChar).checked == false && document.getElementById('company_sub_create'+lastChar).checked == true) {
            $('#company_sub_view'+lastChar).prop('checked', true);
         }
});

$('.company_update').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal.substr(IdVal.length - 2);
   if(document.getElementById('company_sub_view'+lastChar).checked == false && document.getElementById('company_sub_update'+lastChar).checked == true) {
            $('#company_sub_view'+lastChar).prop('checked', true);
         }
});

$('.company_delete').on( "click", function() {
   var IdVal=($(this).attr('id'));
   var lastChar = IdVal.substr(IdVal.length - 2);
   if(document.getElementById('company_sub_view'+lastChar).checked == false && document.getElementById('company_sub_delete'+lastChar).checked == true) {
            $('#company_sub_view'+lastChar).prop('checked', true);
         }
});

$('#master_create').on( "click", function() {
          
         if(document.getElementById('master_create').checked == true) {
            $('.master_create').prop('checked', true);
         } 
         if(document.getElementById('master_create').checked == false) {
              $('.master_create').prop('checked', false);
         }
    });

$('#user_create').on( "click", function() {
          
         if(document.getElementById('user_create').checked == true) {
            $('.user_create').prop('checked', true);
         } 
         if(document.getElementById('user_create').checked == false) {
              $('.user_create').prop('checked', false);
         }
    });

$('#company_create').on( "click", function() {
          
         if(document.getElementById('company_create').checked == true) {
            $('.company_create').prop('checked', true);
         } 
         if(document.getElementById('company_create').checked == false) {
              $('.company_create').prop('checked', false);
         }
    });

$('#master_update').on( "click", function() {
          
         if(document.getElementById('master_update').checked == true) {
            $('.master_update').prop('checked', true);
         } 
         if(document.getElementById('master_update').checked == false) {
              $('.master_update').prop('checked', false);
         }
    });

$('#user_update').on( "click", function() {
          
         if(document.getElementById('user_update').checked == true) {
            $('.user_update').prop('checked', true);
         } 
         if(document.getElementById('user_update').checked == false) {
              $('.user_update').prop('checked', false);
         }
    });

$('#company_update').on( "click", function() {
          
         if(document.getElementById('company_update').checked == true) {
            $('.company_update').prop('checked', true);
         } 
         if(document.getElementById('company_update').checked == false) {
              $('.company_update').prop('checked', false);
         }
    });

$('#master_delete').on( "click", function() {
          
         if(document.getElementById('master_delete').checked == true) {
            $('.master_delete').prop('checked', true);
         } 
         if(document.getElementById('master_delete').checked == false) {
              $('.master_delete').prop('checked', false);
         }
    });

$('#user_delete').on( "click", function() {
          
         if(document.getElementById('user_delete').checked == true) {
            $('.user_delete').prop('checked', true);
         } 
         if(document.getElementById('user_delete').checked == false) {
              $('.user_delete').prop('checked', false);
         }
    });

$('#company_delete').on( "click", function() {
          
         if(document.getElementById('company_delete').checked == true) {
            $('.company_delete').prop('checked', true);
         } 
         if(document.getElementById('company_delete').checked == false) {
              $('.company_delete').prop('checked', false);
         }
    });

$('#master_view').on( "click", function() {
          
         if(document.getElementById('master_view').checked == true) {
            $('.master_view').prop('checked', true);
         } 
         if(document.getElementById('master_view').checked == false) {
              $('.master_view').prop('checked', false);
         }
    });

$('#user_view').on( "click", function() {
          
         if(document.getElementById('user_view').checked == true) {
            $('.user_view').prop('checked', true);
         } 
         if(document.getElementById('user_view').checked == false) {
              $('.user_view').prop('checked', false);
         }
    });

$('#company_view').on( "click", function() {
          
         if(document.getElementById('company_view').checked == true) {
            $('.company_view').prop('checked', true);
         } 
         if(document.getElementById('company_view').checked == false) {
              $('.company_view').prop('checked', false);
         }
    });


});


</script>
@stop