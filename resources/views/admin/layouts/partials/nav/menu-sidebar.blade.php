<!-- <ul class="side-nav metismenu" id="menu">
    @foreach(config('menu.sidebar') as $menu)
        <li class="{{set_active($menu['active'],'active')}}">
            <a href="{{url($menu['link'])}}"><i class="{{$menu['icon']}}"></i> {{$menu['title']}} @if(isset($menu['children']))<span class="icon-fa arrow icon-fa-fw"></span> @endif</a>
            @if(isset($menu['children']))
                <ul aria-expanded="true" class="collapse">
                    @foreach($menu['children'] as $child)
                        <li class="{{set_active($child['active'],'active')}}">
                            <a href="{{url($child['link'])}}">{{$child['title']}}@if(isset($child['children']))<span class="icon-fa arrow icon-fa-fw"></span> @endif</a>
                            @if(isset($child['children']))
                                <ul aria-expanded="true" class="collapse submenu">
                                    @foreach($child['children'] as $subchild)
                                        <li class="{{set_active($subchild['active'])}}"><a href="{{url($subchild['link'])}}">{{$subchild['title']}}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul> -->

@if($menuLists)
  <?php //print_r($menuLists); exit(); ?>
<ul class="side-nav metismenu">
    @foreach($menuLists as $menus)
     
     @if(checkPermission(Auth::user()->id,'view',$menus['menuId']))
   <li {{ (Request::segment(1)==$menus['menuShortUrl'] && !(Request::segment(2)))  ? 'class=active' : '' }}><a href="@if($menus['menuUrl']!='' && $menus['menuUrl']!='NULL'){{url($menus['menuUrl'])}}@else javascript:void(0); @endif">
  <span class="mn_ic"><img src="{{asset($menus['menuImage'])}}" style="vertical-align: middle;" alt=""></span><span>{{$menus['menuName']}}</span></a>
      
      @if($menus['isChild']==1)
        <ul>
        @foreach($menus['childlist'] as $childmenu)
            @if(checkPermission(Auth::user()->id,'view',$childmenu['menuId']))
           <li {{ (Request::segment(2)==$childmenu['menuShortUrl'])  ? 'class=active' : '' }}><a href="@if($childmenu['menuUrl']!='' && $childmenu['menuUrl']!='NULL') {{url($childmenu['menuUrl'])}} @else javascript:void(0); @endif"><span class="mn_ic"><img src="{{asset($childmenu['menuImage'])}}" style="vertical-align: middle;" alt=""></span><span>{{$childmenu['menuName']}}</span></a></li>
            @endif
            
        @endforeach
        </ul>
     @endif
     </li>
     @endif
    

  <!-- <li {{ (Request::segment(2)=='users')  ? 'class=active' : '' }}><a href="{{url('admin/users')}}"><span class="mn_ic"><img src="{{asset('images/menu-admin.png')}}" style="vertical-align: middle;" alt=""></span><span>Administrator</span> </a></li>
 -->
    @endforeach

    <li><a href="{{url('logout')}}"><span class="mn_ic"><img src="{{asset('images/mn14.png')}}" style="vertical-align: middle;" alt=""></span><span>Logout</span></a></li>
</ul>    

@endif

<?php /* ?>
<ul class="side-nav metismenu">

<li {{ (Request::segment(1)=='admin' && !(Request::segment(2)))  ? 'class=active' : '' }}><a href="{{url('admin/')}}">
<span class="mn_ic"><img src="{{asset('images/menu-home.png')}}" style="vertical-align: middle;" alt=""></span><span>Dashboard</span></a></li>

@if((checkPermission(Auth::user()->id,'view',$admin_user_id)) || (checkPermission(Auth::user()->id,'view',$userroles_id)))
<li {{ (Request::segment(2)=='users' || Request::segment(2)=='userroles')  ? 'class=active' : '' }}><a href="javascript:void(0)"><span class="mn_ic"><img src="{{asset('images/menu-admin.png')}}" style="vertical-align: middle;" alt=""></span><span>Administrator</span> </a>
<ul>
  @if(checkPermission(Auth::user()->id,'view',$admin_user_id))
  <li {{ (Request::segment(2)=='users')  ? 'class=active' : '' }}><a href="{{url('admin/users')}}"><span class="mn_ic"><img src="{{asset('images/menu-customer.png')}}" style="vertical-align: middle;" alt=""></span><span>Admin User List</span></a></li>
  @endif

  @if(checkPermission(Auth::user()->id,'view',$userroles_id))
  <li {{ (Request::segment(2)=='usersroles')  ? 'class=active' : '' }}><a href="{{url('admin/usersroles')}}"><span class="mn_ic"><img src="{{asset('images/menu-customer.png')}}" style="vertical-align: middle;" alt=""></span><span>Roles & Right Management</span></a></li>
  @endif

</ul>    
</li>
@endif

@if((checkPermission(Auth::user()->id,'view',$customer_list_id)) || (checkPermission(Auth::user()->id,'view',$business_users_id)) || (checkPermission(Auth::user()->id,'view',$business_users_id)) || (checkPermission(Auth::user()->id,'view',$area_of_interest_id)) || (checkPermission(Auth::user()->id,'view',$level_management_id)) || (checkPermission(Auth::user()->id,'view',$bank_management_id)) || (checkPermission(Auth::user()->id,'view',$id_proof_id)) || (checkPermission(Auth::user()->id,'view',$country_management_id)) || (checkPermission(Auth::user()->id,'view',$state_management_id)) || (checkPermission(Auth::user()->id,'view',$city_management_id)))
<li {{ (Request::segment(2)=='areaofinterest' || Request::segment(2)=='productcategory' || Request::segment(2)=='idprooftype' || Request::segment(2)=='servicetype' || Request::segment(2)=='bank' || Request::segment(2)=='ratetype' || Request::segment(2)=='country' || Request::segment(2)=='state' || Request::segment(2)=='city') ? 'class=active' : '' }}><a href="#"><span class="mn_ic"><img src="{{asset('images/menu-master.png')}}" style="vertical-align: middle;" alt=""></span><span>Masters</span></a><ul>

@if(checkPermission(Auth::user()->id,'view',$customer_list_id))
<li {{ (Request::segment(2)=='customer')  ? 'class=active' : '' }}><a href="{{url('admin/customer')}}"><span class="mn_ic"><img src="{{asset('images/menu-customer.png')}}" style="vertical-align: middle;" alt=""></span><span> Customer List</span></a></li>
@endif

@if(checkPermission(Auth::user()->id,'view',$business_users_id))
<li {{ (Request::segment(2)=='businessusers')  ? 'class=active' : '' }}><a href="{{url('admin/businessusers')}}"><span class="mn_ic"><img src="{{asset('images/menu-Grocery-Category.png')}}" style="vertical-align: middle;" alt=""></span><span>Business Users Management</span></a></li>
@endif

<li {{ (Request::segment(2)=='consumermanager')  ? 'class=active' : '' }}><a href="{{url('admin/consumermanager')}}"><span class="mn_ic"><img src="{{asset('images/menu-customer.png')}}" style="vertical-align: middle;" alt=""></span><span>Consumer Management</span></a></li>

@if(checkPermission(Auth::user()->id,'view',$area_of_interest_id))
<li {{ (Request::segment(2)=='areaofinterest')  ? 'class=active' : '' }}><a href="{{url('admin/areaofinterest')}}"><span class="mn_ic"><img src="{{asset('images/menu-Grocery-Category.png')}}" style="vertical-align: middle;" alt=""></span><span>Area of Interest</span></a></li>
@endif

<li {{ (Request::segment(2)=='industrycategory')  ? 'class=active' : '' }}><a href="{{url('admin/industrycategory')}}"><span class="mn_ic"><img src="{{asset('images/menu-Grocery-Category.png')}}" style="vertical-align: middle;" alt=""></span><span>Industry Category</span></a></li>

@if(checkPermission(Auth::user()->id,'view',$level_management_id))
<li {{ (Request::segment(2)=='levelmanagement')  ? 'class=active' : '' }}><a href="{{url('admin/levelmanagement')}}"><span class="mn_ic"><img src="{{asset('images/menu-Grocery-Category.png')}}" style="vertical-align: middle;" alt=""></span><span>Level Management</span></a></li>
@endif

@if(checkPermission(Auth::user()->id,'view',$bank_management_id))
<li {{ (Request::segment(2)=='bank')  ? 'class=active' : '' }}><a href="{{url('admin/bank')}}"><span class="mn_ic"><img src="{{asset('images/bank.png')}}" style="vertical-align: middle;" alt=""></span><span>Bank</span></a></li>
@endif


@if(checkPermission(Auth::user()->id,'view',$id_proof_id)) 
<li {{ (Request::segment(2)=='idprooftype')  ? 'class=active' : '' }}><a href="{{url('admin/idprooftype')}}"><span class="mn_ic"><img src="{{asset('images/id-proof.png')}}" style="vertical-align: middle;" alt=""></span><span>Id Proof Type</span></a></li>
@endif

@if(checkPermission(Auth::user()->id,'view',$country_management_id))
<li {{ (Request::segment(2)=='country')  ? 'class=active' : '' }}><a href="{{url('admin/country')}}"><span class="mn_ic"><img src="{{asset('images/menu-country.png')}}" style="vertical-align: middle;" alt=""></span><span>Country Management</span></a></li>
@endif

@if(checkPermission(Auth::user()->id,'view',$state_management_id))
<li {{ (Request::segment(2)=='state')  ? 'class=active' : '' }}><a href="{{url('admin/state')}}"><span class="mn_ic"><img src="{{asset('images/menu-state.png')}}" style="vertical-align: middle;" alt=""></span><span>State Management</span></a></li>
@endif

@if(checkPermission(Auth::user()->id,'view',$city_management_id))
<li {{ (Request::segment(2)=='city')  ? 'class=active' : '' }}><a href="{{url('admin/city')}}"><span class="mn_ic"><img src="{{asset('images/menu-city.png')}}" style="vertical-align: middle;" alt=""></span><span>City Management</span></a></li>
@endif


</ul>
</li>
@endif

@if(checkPermission(Auth::user()->id,'view',$mission_management_id))
 <li {{ (Request::segment(2)=='missionmanagement')  ? 'class=active' : '' }}><a href="{{url('admin/missionmanagement')}}"><span class="mn_ic"><img src="{{asset('images/menu-customer.png')}}" style="vertical-align: middle;" alt=""></span><span>Mission Management</span></a></li>
@endif

@if((checkPermission(Auth::user()->id,'view',$system_settings_id)) || (checkPermission(Auth::user()->id,'view',$general_message_id)) || (checkPermission(Auth::user()->id,'view',$version_management_id)))
<li {{ (Request::segment(2)=='setting' || Request::segment(2)=='languages' || Request::segment(2)=='generalmessage' || Request::segment(2)=='notificationmessage' || Request::segment(2)=='generalnotification' || Request::segment(2)=='version' || Request::segment(2)=='howdidyouknow') ? 'class=active' : '' }}><a href="javascript:void(0)"><span class="mn_ic"><img src="{{asset('images/menu-setting.png')}}" style="vertical-align: middle;" alt=""></span><span>Setting</span></a> 
<ul>

@if(checkPermission(Auth::user()->id,'view',$system_settings_id))
<li {{ (Request::segment(2)=='setting')  ? 'class=active' : '' }}><a href="{{url('admin/setting')}}"><span class="mn_ic"><img src="{{asset('images/menu-configuration.png')}}" style="vertical-align: middle;" alt=""></span><span>System Settings</span></a></li> 
@endif


@if(checkPermission(Auth::user()->id,'view',$general_message_id))
<li {{ (Request::segment(2)=='generalmessage')  ? 'class=active' : '' }}><a href="{{url('admin/generalmessage')}}"><span class="mn_ic"><img src="{{asset('images/menu-message.png')}}" style="vertical-align: middle;" alt=""></span><span>General Message</span></a></li>
@endif


@if(checkPermission(Auth::user()->id,'view',$version_management_id))
<li {{ (Request::segment(2)=='version')  ? 'class=active' : '' }}><a href="{{url('admin/version')}}"><span class="mn_ic"><img src="{{asset('images/Version-Management.png')}}" style="vertical-align: middle;" alt=""></span><span>Version Management</span></a></li>
@endif

</ul></li>
@endif

<li><a href="{{url('logout')}}"><span class="mn_ic"><img src="{{asset('images/mn14.png')}}" style="vertical-align: middle;" alt=""></span><span>Logout</span></a></li>


</ul>
<?php */ ?>