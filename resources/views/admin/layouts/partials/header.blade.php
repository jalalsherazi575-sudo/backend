<header class="site-header">
  <a href="#" class="brand-main" style="color:#7A7A62;">
   Medfellows App
    <img src="{{asset('/assets/admin/img/favicon.png')}}" id="logo-desk" alt="Medfellows App Logo" class="d-none d-md-inline ">
    <img src="{{asset('/assets/admin/img/favicon.png')}}" id="logo-mobile" alt="Medfellows App Logo" class="d-md-none">
    <!-- <img src="{{asset('/assets/admin/img/long-logo.png')}}" id="logo-desk" alt="Laraspace Logo" class="d-none d-md-inline ">
    <img src="{{asset('/assets/admin/img/long-logo.png')}}" id="logo-mobile" alt="Laraspace Logo" class="d-md-none"> -->
  </a>
  <a href="#" class="nav-toggle">
    <div class="hamburger hamburger--htla">
      <span>toggle menu</span>
    </div>
  </a>

    <ul class="action-list">
      
      <li>
        <span style="color:#7A7A62;margin-right:15px;">Welcome {{Auth::user()->name}}</span>
        <a href="{{url('admin/users/edit')}}/{{Auth::user()->id}}"  class="avatar"><img src="{{asset('/assets/admin/img/avatars/man-user.png')}}" alt="Avatar"></a>
       
      </li>
    </ul>
</header>
