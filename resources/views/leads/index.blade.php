@extends('layouts.admin.app')

@section('content')

    <!-- Main content -->
    <section class="content">
      <div class="row">
      @if(Auth::user()->role == 'admin')
	  <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3>{{$unAssignedLeads}}</h3>
              <p>Holding Leads</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{url('/leads?type=holding')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3>{{$allLeads-$unAssignedLeads}}</h3>
              <p>Assigned Leads</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{url('/leads?type=assigned')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3>{{$allLeads}}</h3>

              <p>All Leads</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{route('leads.index')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        @endif
        <!-- ./col -->
        <div class="col-xs-12">
          <div class="">
            <div class="box-header">
              <h3 class="box-title">Leads Listing</h3>
              <div class="box-tools">
                <a href="{{route('leads.create')}}" class="btn btn-success"><i class="fa fa-plus"></i> Add New Lead</a>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box box-body table-responsive">
            {{ Form::open(['url' => route('leads.index'),'method' => 'get']) }}
                  <div class="row">
                  <div class="col-lg-<?php if(Auth::user()->role == 'admin') echo '2'; else echo '3'; ?>">
                  {!! Form::hidden('type', app('request')->query('type')) !!}

                              {{ Form::select('lead_status', getLeadStatus(), app('request')->query('lead_status'), ['class' => 'form-control']) }}
                      </div>
                      @if(Auth::user()->role == 'admin')
                        <div class="col-md-3 form-group">
                          {!! Form::select('user_id', getAgentsList(), app('request')->query('user_id'),['class' => 'form-control select2','id'=>'user_id'] ) !!}
                        </div>
                      @endif
                      <!--<div class="col-md-<?php if(Auth::user()->role == 'admin') echo '2'; else echo '3'; ?> form-group">
                        {!! Form::text('start_dt', app('request')->query('start_dt'),['placeholder'=>'Start date','autocomplete'=>"off",'class' => 'datepicker-class form-control','id'=>'start_dt'] ) !!}
                      </div>
                      <div class="col-md-<?php if(Auth::user()->role == 'admin') echo '2'; else echo '3'; ?> form-group">
                        {!! Form::text('end_dt', app('request')->query('end_dt'),['placeholder'=>'End date','autocomplete'=>"off",'class' => 'datepicker-class form-control','id'=>'end_dt'] ) !!}
                      </div>-->
                      <div class="col-lg-3">
                          <button class="btn btn-success" title="Search" type="submit"><i class="fa fa-filter"></i> Filter</button>
                          <a href="{{ route('leads.index') }}" class="btn btn-warning" title="Cancel"><i class="fa fa-fw fa-refresh"></i> Reset</a>
                      </div>
                  </div>
                {{ Form::close() }}
              <table id="leads-listing" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Client Name</th>
                 
                  <th>Category</th>
                   @if(Auth::user()->role == 'admin')
                    <th>Assigned Agent</th>
                  @endif
                  <th>Email</th>
                  <th>Lead Status</th>
                  <!--<th>Move In Date</th>-->
                  <th class="no-sort">Action</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Client Name</th>
                  
                  <th>Category</th>
                  @if(Auth::user()->role == 'admin')
                    <th>Assigned Agent</th>
                  @endif
                  <th>Email</th>
                  <th>Lead Status</th>
                  <!--<th>Move In Date</th>-->
                  <th >Action</th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    
    <!-- /.content -->
	@section('pagescript')
  <style>
      .datepicker-orient-top{
          z-index:9999 !important;
      }
  </style>
		<script>
    $(document).ready( function () {
      $('#daterange-btn span').html('<?php echo $startDt;?>' + ' - ' + '<?php echo $endtDt;?>');
      //Initialize Select2 Elements
   	$('.select2').select2();
     $('.datepicker-class').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
        
      });
    $('#leads-listing').DataTable({
           processing: true,
           serverSide: true,
         //  aaSorting: [[2, 'desc']],
           language: {
              searchPlaceholder: "eg. client name,email"
          },
           //ajax: "{{ url('leads-list?type='.app('request')->input('type').'&lead_status='.app('request')->query('lead_status')) }}",
           ajax: {
                url: "{{ url('leads-list') }}",
                type: 'GET',
                data: function (d) {
                    d.type = "{{app('request')->input('type')}}",
                    d.lead_status = "{{app('request')->input('lead_status')}}",
                    d.user_id = "{{app('request')->input('user_id')}}"
                },
            },           
           columns: [
                    { data: 'id', name: 'id' },
                    { data: 'full_name', name: 'full_name' ,"bSortable": false },
                     {data: 'lead_detail.category', "bSortable": false,name: 'lead_detail.category'},
                  
                      <?php if(Auth::user()->role == 'admin'){ ?>{
                        "name" : "user_id",
                        "data" : "agent_name"
                    },
                    <?php } ?>
                    { data: 'email', name: 'email' },
                    { data: 'lead_status_label', name: 'lead_status_label',"bSortable": false },
//                    { data: 'move_in_date', name: 'move_in_date' },
                    { "bSortable": false, data: 'action', name: 'action'}
                 ],
        });
     });
		</script>
	@stop

@endsection
