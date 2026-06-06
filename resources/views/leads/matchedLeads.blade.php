@extends('layouts.admin.app')

@section('content')

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- ./col -->
        <div class="col-xs-12">
        <h4>Property: {{$property->post_title}}</h4>
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Matched  Leads Listing</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table id="property-matched-leads" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Client Name</th>
                  <th>Email</th>
                  <th>Lead Status</th>
                  <th>Move In Date</th>
                  <th class="no-sort">Action</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Client Name</th>
                  <th>Email</th>
                  <th>Lead Status</th>
                  <th>Move In Date</th>
                  <th class="no-sort">Action</th>
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
		<script>
    $(document).ready( function () {
      $('#property-matched-leads').DataTable({
           processing: true,
           serverSide: true,
		   "searching": false,
           language: {
              searchPlaceholder: "eg. title,owner name,price,address"
          },
           ajax: {
                url: "{{ url('matched-leads-ajax') }}",
                type: 'GET',
                data: function (d) {
                    d.propertyId = "{{app('request')->input('property-id')}}"
                },
            },           
           columns: [
                    { data: 'id', name: 'id' },
                    { data: 'full_name', name: 'full_name' },
                    { data: 'email', name: 'email' },
                    { "searchable" : false, "bSortable": false,data: 'lead_status_label', name: 'lead_status_label' },
                    { data: 'move_in_date', name: 'move_in_date' },
                    { "bSortable": false, data: 'action', name: 'action' }
                 ],
        });
        $('.dataTables_filter input').width(200);
     });
		</script>
	@stop

@endsection
