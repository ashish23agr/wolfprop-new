@extends('layouts.admin.app')

@section('content')

    <!-- Main content -->
    <section class="content">
      <div class="row">     
        <!-- ./col -->
        <div class="col-xs-12">
            <div class="box-header">
              <h3 class="box-title">Deleted Properties</h3>
            </div>
            <!-- /.box-header -->
            <div class="box box-body table-responsive">
              <table id="bookmarked-properties" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Agent Name</th>
                  <th>Price</th>
                  <th>Address</th>
                  <th class="no-sort">Action</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Agent Name</th>
                  <th>Price</th>
                  <th>Address</th>
                  <th class="no-sort">Action</th>
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
     

      $('#bookmarked-properties').DataTable({
           processing: true,
           serverSide: true,
           language: {
              searchPlaceholder: "eg. title,owner name,price,address"
          },
           ajax: {
                url: "{{ url('deleted-properties-list') }}",
                type: 'GET',
                data: function (d) {
                    d.leadid = "{{app('request')->input('lead-id')}}"
                },
            },           
           columns: [
                    { "searchable" : false,data: 'ID', name: 'ID' },
                    {
                        "name": "postDetail.image",
                        "data": "post_detail.image",
                        "render": function (data, type, full, meta) {
                             if(data==null) data='https://wolfproperties.projectstatus.in/laravel/no_image.png';
                            return "<img src=\"" + data + "\" height=\"50\" width=\"80\" />";
                        },
                        "title": "Image",
                        "searchable" : false,
                        "bSortable": false,
                    },
                    { "searchable" : false,"bSortable": false,data: 'post_detail.post_title', name: 'post_detail.post_title' },
                    { "searchable" : false,"bSortable": false, data: 'post_detail.author.display_name', name: 'display_name' },
                    { "searchable" : false, data: 'price', name: 'price' },
                    { "searchable" : false, "bSortable": false,data: 'address', name: 'address' },
                    { "bSortable": false,"bSortable": false, data: 'action', name: 'action' }
                 ],
        });
        $('.dataTables_filter input').width(200);
     });
		</script>
	@stop

@endsection
