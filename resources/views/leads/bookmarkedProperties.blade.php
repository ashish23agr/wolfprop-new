@extends('layouts.admin.app')

@section('content')

    <!-- Main content -->
    <section class="content">
      <div class="row">     
        <!-- ./col -->
        <div class="col-xs-12">
          <div>
            <div class="box-header">
              <h3 class="box-title">Bookmarked Properties</h3>
            </div>
            <!-- /.box-header -->
            <div class="box box-body table-responsive">
            {{ Form::open(['url' => route('bookmarked-properties'),'method' => 'get']) }}
              <div class="row">
              {!! Form::hidden('lead-id', app('request')->query('lead-id')) !!}
                <div class="col-lg-3">
                      {{ Form::select('bedroom', getBedrooms(true), app('request')->query('bedroom'), ['class' => 'form-control']) }}
                  </div>
                    <div class="col-md-3">
                      {!! Form::select('bathroom', getBathrooms(true), app('request')->query('bathroom'),['class' => 'form-control','id'=>'bathroom'] ) !!}
                    </div>
                    <div class="col-md-3 form-group">
                      {!! Form::select('neighborhood', getNeighbourhood(), app('request')->query('neighborhood'),['multiple'=>'multiple','class' => 'select-neighbourhood form-control','id'=>'neighborhood'] ) !!}
                    </div>
                  <div class="col-lg-3 ">
                      <button class="btn btn-success" title="Submit" type="submit"><i class="fa fa-filter"></i> Filter</button>
                      <a href="{{ url('bookmarked-properties') }}" class="btn btn-warning" title="Cancel"><i class="fa fa-fw fa-refresh"></i> Reset</a>
                  </div>
              </div>
            {{ Form::close() }}
              <table id="bookmarked-properties" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th width="300px">Title</th>
                  <th>Info</th>
                  <th>Client Name</th>
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
                  <th>Client Name</th>
                  <th>Agent Name</th>
                  <th>Price</th>
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
      $('.select2').select2();
      $('.select-neighbourhood').multiselect({
        selectAll: true,
        placeholder: 'Select Neighborhood',
        search: true
      });
      $('#bookmarked-properties').DataTable({
           processing: true,
           serverSide: true,
           language: {
              searchPlaceholder: "eg. id"
          },
           ajax: {
                url: "{{ url('bookmarked-properties-list') }}",
                type: 'GET',
                data: function (d) {
                    d.leadid = "{{app('request')->input('lead-id')}}"
                    d.bedroom = "{{app('request')->input('bedroom')}}",
                    d.bathroom = "{{app('request')->input('bathroom')}}",
                    d.neighborhood = "{{app('request')->input('neighborhood')}}"
                },
            },           
           columns: [
                    { data: 'ID', name: 'ID' },
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
                    { "searchable" : false, "bSortable": false,data: 'post_detail.post_title', name: 'post_detail.post_title' },
                    { "searchable" : false, "bSortable": false,data: 'info', name: 'info' },
                    { "searchable" : false,"bSortable": false,data: 'client_name', name: 'client_name' },
                    { "searchable" : false,"bSortable": false, data: 'post_detail.author.display_name', name: 'post_detail.author.display_name' },
                    { "searchable" : false,  "bSortable": false,data: 'price', name: 'price' },
                    { "bSortable": false,"bSortable": false, data: 'action', name: 'action' }
                 ],
        });
        $('.dataTables_filter input').width(200);
     });
		</script>
	@stop

@endsection
