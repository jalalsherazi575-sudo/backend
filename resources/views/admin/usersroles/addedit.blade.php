@extends('admin.layouts.layout-basic')

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

@section('content')
        <div class="main-content">
            <div class="page-header center">
                <h3 class="page-title">@if(isset($users)) Edit @else Add @endif User Role</h3>
                <ol class="breadcrumb">
                    
                    <li class="breadcrumb-item active">@if(isset($users)) Edit @else Add @endif User Role</li>
                </ol>
            </div>
            
                 <div class="row">
                  <div class="col-sm-12">  
                     <div class="card center">
                        <div class="card-body">

                            <form class="form-horizontal" id="validateForm" enctype="multipart/form-data" method="post" action="@if(isset($users)){{ URL::to('admin/usersroles/edit/'.$users->role_id.'')}}@else{{ URL::to('admin/usersroles/create') }}@endif" name="user">
                                @csrf
                                @if(isset($users))
                               <input type="hidden" name="edit_id" value="{{$users->role_id}}"> 
                               @endif

                                <div class="form-group">
                                <label>Role Name<span class="req">*</span></label>
                                <input type="text" @if(isset($users) && $users->role_name=='Consumer Manager') readonly="readonly" @endif maxlength="40" class="form-control" value="@if(isset($users)){{$users->role_name}}@endif" name="role_name"  id="role_name" placeholder="Please Enter Role Name">
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
                                     <?php //print_r($menuLists); exit(); ?>
                                    @if($menuLists)
                                           @foreach($menuLists as $menus)
                                              
                                               
                                               <tr>
                                                <td><strong>{{$menus['menuName']}}</strong></td>
                                                <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getCreateModuleValue($menus['menuId'],$users->role_id))?'checked':'') }}@endif class="master_create" name="create[{{$menus['menuId']}}]" id="master_create{{$menus['menuId']}}" value="1"></td>
                                                <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_update" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getUpdateModuleValue($menus['menuId'],$users->role_id))?'checked':'') }}@endif name="update[{{$menus['menuId']}}]" id="master_update{{$menus['menuId']}}" value="1"></td>
                                                <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_delete" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getDeleteModuleValue($menus['menuId'],$users->role_id))?'checked':'') }}@endif name="delete[{{$menus['menuId']}}]" id="master_delete{{$menus['menuId']}}" value="1"></td>
                                                <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_view" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getViewModuleValue($menus['menuId'],$users->role_id))?'checked':'') }}@endif name="view[{{$menus['menuId']}}]" id="master_view{{$menus['menuId']}}" value="1"></td>
                                              </tr>

                                              @if($menus['isChild']==1)
                                                 @foreach($menus['childlist'] as $childmenu)
                                                <tr>
                                                  <td>{{$childmenu['menuName']}}</td>
                                                  <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getCreateModuleValue($childmenu['menuId'],$users->role_id))?'checked':'') }}@endif class="user_create" name="create[{{$childmenu['menuId']}}]" id="user_sub_create{{$childmenu['menuId']}}" value="1"></td>
                                                  <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox"  @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getUpdateModuleValue($childmenu['menuId'],$users->role_id))?'checked':'') }}@endif class="user_update" name="update[{{$childmenu['menuId']}}]" id="user_sub_update{{$childmenu['menuId']}}" value="1"></td>
                                                  <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox"  @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getDeleteModuleValue($childmenu['menuId'],$users->role_id))?'checked':'') }}@endif class="user_delete" name="delete[{{$childmenu['menuId']}}]" id="user_sub_delete{{$childmenu['menuId']}}" value="1"></td>
                                                  <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getViewModuleValue($childmenu['menuId'],$users->role_id))?'checked':'') }}@endif class="user_view" name="view[{{$childmenu['menuId']}}]" id="user_sub_view{{$childmenu['menuId']}}" value="1"></td>
                                                </tr>
                                                  @endforeach

                                              @endif


                                           @endforeach
                                    @endif
                                    
                                   <?php /* ?> 
                                    
                                    @if ($master_module_list)
                                      
                                      @foreach ($master_module_list as $lists)
                                    <tr>
                                      <td>{{$lists->module_name}}</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getCreateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="master_create" name="create[{{$lists->module_id}}]" id="master_create{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_update" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getUpdateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="update[{{$lists->module_id}}]" id="master_update{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_delete" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getDeleteModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="delete[{{$lists->module_id}}]" id="master_delete{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" class="master_view" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getViewModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif name="view[{{$lists->module_id}}]" id="master_view{{$lists->module_id}}" value="1"></td>
                                    </tr>
                                       @endforeach    
                                       
                                        @endif  
                                    
                                    
                                    @if ($user_module)
                                    @foreach ($user_module as $lists)
                                    <tr>
                                      <td>@if($lists->parent_id==0) <strong>{{$lists->module_name}}</strong> @else {{$lists->module_name}} @endif</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?></td>
                                    </tr>
                                    @endforeach 
                                     @endif
                                    
                                    @if ($user_module_list)
                                      
                                      @foreach ($user_module_list as $lists)
                                    <tr>
                                      <td>{{$lists->module_name}}</td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getCreateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_create" name="create[{{$lists->module_id}}]" id="user_sub_create{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox"  @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getUpdateModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_update" name="update[{{$lists->module_id}}]" id="user_sub_update{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox"  @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getDeleteModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_delete" name="delete[{{$lists->module_id}}]" id="user_sub_delete{{$lists->module_id}}" value="1"></td>
                                      <td><?php echo str_repeat("&nbsp;",5)?><input type="checkbox" @if(isset($users)){{(! empty(Laraspace\Http\Controllers\Controller::getViewModuleValue($lists->module_id,$users->role_id))?'checked':'') }}@endif class="user_view" name="view[{{$lists->module_id}}]" id="user_sub_view{{$lists->module_id}}" value="1"></td>
                                    </tr>
                                       @endforeach    
                                       
                                        @endif  
                                   
                                    <?php */ ?>



                                  </tbody>
                                  
                            </table>   
                            </div>

                            <button class="btn btn-primary">Submit</button>
                          </form>
                        
                        </div>
                      </div>
                   </div>
                  </div>        



        </div>    
    

    
@stop
