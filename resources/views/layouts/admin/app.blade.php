<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Custom developer css -->
	<link rel="stylesheet" href="{{ asset('css/dev-css.css') }}">
    <!-- Bootstrap 3.3.7 -->
    @if(Auth::user()->role == 'admin')
        <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/Admin-bootstrap.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/Agent-bootstrap.min.css') }}">
    @endif
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
     <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
	<!-- bootstrap datepicker -->
  <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
	<!-- DataTables -->
	<link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
	<!-- Select2 -->
	<link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    
    @if(Auth::user()->role == 'admin')
        <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">    
    @else
        <link rel="stylesheet" href="{{ asset('dist/css/skins/agent_all-skins.min.css') }}">
    @endif
    <!-- AdminLTE Skins. Choose a skin from the css/skins
        folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('css/jquery.multiselect.css') }}">

    <style>
        /*
            these styles will animate bootstrap alerts.
        */
        .alert{
            z-index: 99;
            top: 60px;
            right:0px;
            min-width:30%;
            position: fixed;
            animation: slide 0.5s forwards;
            margin-top:20px;
        }

        @keyframes slide {
            100% { top: 30px; }
        }

        @media screen and (max-width: 668px) {
            .alert{ /* center the alert on small screens */
                left: 10px;
                right: 10px; 
            }
        }
    </style>

    <title>{{config('app.name')}}</title>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">   
    @include('inc.admin.header')
    @include('inc.admin.sidebar')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible show"> 
            {!! session('success') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible show"> 
            {!! session('error') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @yield('content')
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- ./wrapper -->
    <!-- jQuery 3 -->
    <script src="{{asset('bower_components/jquery/dist/jquery.min.js')}}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{asset('bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
	<!-- Select2 -->
	<script src="{{asset('bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <!-- date-range-picker -->
    <script src="{{asset('bower_components/moment/min/moment.min.js')}}"></script>
    <script src="{{asset('bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
	<!-- bootstrap datepicker -->
	<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
	<!-- DataTables -->
	<script src="{{asset('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <!-- FastClick -->
    <script src="{{asset('bower_components/fastclick/lib/fastclick.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('dist/js/adminlte.min.js')}}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{asset('dist/js/demo.js')}}"></script>
    <script src="{{asset('js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('js/jquery.multiselect.js')}}"></script>
    
    {{-- Success Alert --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{session('status')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{session('error')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
	@if ($errors->any())
		<div class="alert alert-danger alert-dismissible show" role="alert">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
		</div>
	@endif
    <script>
        //close the alert after 3 seconds.
        $(document).ready(function(){
			setTimeout(function() {
	        	$(".alert").alert('close');
	    	}, 3000);
    	});
    </script>
    @yield('pagescript')
</body>
</html>