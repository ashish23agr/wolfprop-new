@extends('layouts.admin.app')

@section('content')

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- ./col -->
        <div class="col-xs-12">
            <div class="box-header">
              <h3 class="box-title">Notifications Listing</h3>
            </div>
            <!-- /.box-header -->
            <div class="box box-body table-responsive">
              <table id="notification-properties" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th width="300px">Title</th>
                  <th>Info</th>
                  <th>Agent Name</th>
                  <th>Price</th>
                  <th class="no-sort">Action</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                   <th>ID</th>
                  <th>Image</th>
                  <th width="300px">Title</th>
                  <th>Info</th>
                  <th>Agent Name</th>
                  <th>Price</th>
                  <th>Action</th>
                </tr>
                </tfoot>
              </table>
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
      $('#notification-properties').DataTable({
           processing: true,
           serverSide: true,
		   "searching": false,
           language: {
              searchPlaceholder: "eg. title,owner name,price,address"
          },
           ajax: {
                url: "{{ url('notification-ajax') }}",
                type: 'GET',
            },           
           columns: [
                    { "searchable" : false,data: 'ID', name: 'ID' },
                    {
                        "name": "image",
                        "data": "image",
                        "render": function (data, type, full, meta) {
                             if(data==null) data='https://wolfproperties.projectstatus.in/laravel/no_image.png';
                            return "<img src=\"" + data + "\" height=\"50\" width=\"80\" />";
                        },
                        "title": "Image",
                        "searchable" : false,
                        "orderable": false,
                    },
                    { "searchable" : false,"bSortable": false,data: 'post_title', name: 'post_title' },
                    { "searchable" : false, "bSortable": false,data: 'info', name: 'info' },
                    { "searchable" : false,"bSortable": false, data: 'author.user_login', name: 'user_login' },
                    { "searchable" : false,"bSortable": false, data: 'price', name: 'price' },
                    { "bSortable": false,"bSortable": false, data: 'action', name: 'action' }
                 ],
        });
        $('.dataTables_filter input').width(200);
     });
		</script>
	@stop

@endsection
