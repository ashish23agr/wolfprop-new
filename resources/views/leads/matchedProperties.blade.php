@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">
      <div class="row">     
        <!-- ./col -->
        <div class="col-xs-12">
          <div class="@if(Auth::user()->role == 'admin') box @endif">
            <div class="box-header">
              <h3 class="box-title">Matched Properties</h3>
       
            </div>
   <div class="box-header">
     
              <strong>Client Name : </strong>{{   $lead->first_name.' '.$lead->last_name }}
              <br>
              <?php if(Auth::user()->role == 'admin') {?> <strong>Assigned Agent Name : </strong>{{   $lead->agent_name }}<?php }?>
            </div>
            
            <!-- /.box-header -->
            <div class="box-body table-responsive">
            {{ Form::open(['url' => route('matched-properties'),'method' => 'get']) }}
              <div class="row">
              {!! Form::hidden('id', app('request')->query('id')) !!}
                <div class="col-lg-3">
                      {{ Form::select('bedroom', getBedrooms(true), app('request')->query('bedroom'), ['class' => 'form-control']) }}
                  </div>
                    <div class="col-md-3">
                      {!! Form::select('bathroom', getBathrooms(true), app('request')->query('bathroom'),['class' => 'form-control','id'=>'bathroom'] ) !!}
                    </div>
                    <div class="col-md-3 form-group">
                    
                      {!! Form::select('neighborhood[]', getNeighbourhood(), [],['multiple'=>'multiple','class' => 'select-neighbourhood form-control','id'=>'neighborhood'] ) !!}
                    </div>
                  <div class="col-lg-3 ">
                      <button class="btn btn-success" title="Submit" type="submit"><i class="fa fa-filter"></i> Filter</button>
                      <a href="{{ url('matched-properties?id='.app('request')->query('id')) }}" class="btn btn-warning" title="Cancel"><i class="fa fa-fw fa-refresh"></i> Reset</a>
                  </div>
              </div>
            {{ Form::close() }}
              <table id="matched-properties-listing" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th width="300px">Title</th>
                  <th>Info</th>
                  <th>Listing Agent</th>
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
                  <th>Listing Agent</th>
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
      var neighbourIdArr = <?php echo json_encode(app('request')->input('neighborhood')); ?>;
      if(neighbourIdArr != null){
        var data = neighbourIdArr.join();
        //Make an array
        var dataarray=data.split(",");
        // Set the value
        $(".select-neighbourhood").val(dataarray);
      }
      $('.select2').select2();
      $('.select-neighbourhood').multiselect({
        selectAll: true,
        placeholder: 'Select Neighborhood',
        search: true
      });
      $('#matched-properties-listing').DataTable({
           processing: true,
           serverSide: true,
           language: {
              //searchPlaceholder: "eg. title,owner name,price,address"
              searchPlaceholder: "eg. id,title,owner name"
          },
           ajax: {
                url: "{{ url('matched-properties-list') }}",
                type: 'GET',
                data: function (d) {
                    d.id = "{{app('request')->input('id')}}",
                    d.bedroom = "{{app('request')->input('bedroom')}}",
                    d.bathroom = "{{app('request')->input('bathroom')}}",
                    d.neighborhood = "{{app('request')->input('neighborhood') ? implode(',',app('request')->input('neighborhood')) : ''}}"                  
                },
            },           
           columns: [
                    { data: 'ID', name: 'ID' },
                    {
                        "name": "image",
                        "data": "image",
                        "render": function (data, type, full, meta) {
                            
                        if(data==null) data='https://wolfproperties.projectstatus.in/laravel/no_image.png';
                            return "<img src=\"" + data + "\" height=\"50\" width=\"80\" />";
                        },
                        "title": "Image",
                        "searchable" : false,
                        "bSortable": false,
                    },
                    { "bSortable": false,data: 'post_title', name: 'post_title' },
                    { "bSortable": false,"searchable" : false, "bSortable": false,data: 'info', name: 'info' },
                    { "bSortable": false, data: 'author.user_login', name: 'author.user_login' },
                    { "bSortable": false,"searchable" : false, data: 'price', name: 'price' },
                    { "bSortable": false, data: 'action', name: 'action' }
                 ],
        });
        $('.dataTables_filter input').width(200);
     });
		</script>
	@stop

@endsection
