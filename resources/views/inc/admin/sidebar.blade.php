<?php 
if(Auth::user()->role == 'member')
$unreadcount =  DB::table('property_notifications')->where(['user_id'=> Auth::user()->ID,'is_read'=>0])->count();
?>
 <!-- Left side column. contains the logo and sidebar -->
 <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <ul class="sidebar-menu" data-widget="tree">
        <!--<li class="header">MAIN NAVIGATION</li>-->
        <li>
        <?php 
        $getMethod = list(, $action) = explode('@', Route::getCurrentRoute()->getActionName());
        $wpAdminUrl =  'https://'.$_SERVER['HTTP_HOST'].'/wp-admin'; //live
        if($_SERVER["REMOTE_ADDR"] == '192.168.4.224'){
          $wpAdminUrl =  'http://'.$_SERVER['HTTP_HOST'].'/wolf-prop/wp-admin'; //local 
        }  
        /*if(Auth::user()->role == 'admin'){ 
            $wpAdminUrl =  'https://'.$_SERVER['HTTP_HOST'].'/wp-admin'; //live
            if($_SERVER["REMOTE_ADDR"] == '192.168.4.224'){
              $wpAdminUrl =  'http://'.$_SERVER['HTTP_HOST'].'/wolf-prop/wp-admin'; //local 
            }
          }else{
            $wpAdminUrl =  'https://'.$_SERVER['HTTP_HOST'].'/user/'.Auth::user()->user_login; //live
            if($_SERVER["REMOTE_ADDR"] == '192.168.4.224'){
              $wpAdminUrl =  'http://'.$_SERVER['HTTP_HOST'].'/wolf-prop/user/'.Auth::user()->user_login; //local 
            }
          } */
         ?>
          <a href="<?php echo $wpAdminUrl;?>">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        <li class="treeview {{ request()->is('*leads*') || (isset($getMethod) && $getMethod[1] == 'propertyDetail') || \Request::route()->getName() == 'matched-properties'  && !request()->is('assign-leads') ? 'active' : '' }}">
          <a href="#">
            <i class="fa fa-tasks"></i> <span>Lead Manager</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ request()->is('leads/create') ? 'active' : '' }}"><a href="{{route('leads.create')}}"><i class="fa fa-plus"></i> Add New Lead</a></li>
            <li class="{{ request()->is('*leads*') || (isset($getMethod) && $getMethod[1] == 'propertyDetail') || \Request::route()->getName() == 'matched-properties' || \Request::route()->getName() == 'leads.show' ? 'active' : '' }}"><a href="{{route('leads.index')}}"><i class="fa fa-tasks"></i> Manage Leads</a></li>
          </ul>
        </li>
        <li class="{{ (isset($getMethod) && $getMethod[1] == 'bookmarkedProperties') ? 'active' : '' }}">
          <a href="{{url('bookmarked-properties')}}">
            <i class="fa fa-check"></i> <span>Bookmarked Properties</span>
          </a>
        </li>
        <li class="{{ (isset($getMethod) && $getMethod[1] == 'deletedProperties') ? 'active' : '' }}">
          <a href="{{url('deleted-properties')}}">
            <i class="fa fa-trash"></i> <span>Deleted Properties</span>
          </a>
        </li>
        @if(Auth::user()->role == 'admin')
          <li class="{{ request()->is('assign-leads') ? 'active' : '' }}">
            <a href="{{route('assign-leads')}}">
              <i class="fa fa-user-plus"></i> <span>Assign Holding Leads</span>
            </a>
          </li>
        @endif
        @if(Auth::user()->role == 'member')
          <li class="{{ request()->is('notification-list') ? 'active' : '' }}">
            <a href="{{url('notification-list')}}">
              <i class="fa fa-bell-o"></i> <span>Notificaiton List <small class="label pull-right bg-red">{{@$unreadcount}}</small></span>
            </a>
          </li>
        @endif
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>