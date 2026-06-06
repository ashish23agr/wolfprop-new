<header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0);" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <img src="{{asset('public/logo.png')}}" class="logo-mini" alt="Logo" height=50>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Wolf</b>Properties</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <span class="login-name" style="color:#fff;padding:25px !important;line-height:50px;font-size:20px;"><?php echo (Auth::user()->display_name) ? ucwords(strtolower(Auth::user()->display_name)) : '';?></span>
    </nav>
  </header>