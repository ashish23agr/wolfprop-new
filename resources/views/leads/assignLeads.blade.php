@extends('layouts.admin.app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
   <h1>
      Assign Holding Leads
      <small>Here you can assign holding leads</small>
   </h1>
   <ol class="breadcrumb">
      <li class="active"><a href="{{route('leads.index')}}"><i class="fa fa-dashboard"></i>Home</a></li>
      <li class="active">Assign Holding lead</li>
   </ol>
</section>
<!-- Main content -->
<section class="content">
   <!-- SELECT2 EXAMPLE -->
   <div class="box box-default">
   @if(count($unAssignedLead) > 0)
      <div class="box-header with-border">
         <h3 class="box-title">Assign Holding Leads</h3>
      </div>
      {!! Form::open(['url' => route('assign-leads'),'id'=>'assignLeads','name'=>'assignLeads']) !!}
      <!-- /.box-header -->
      <div class="box-body">
         <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label for="first_name">Select Holding Leads <span class="mandatoryClass">*</span></label>
                  <!--<select name="leads[]" multiple="multiple" class="3col active">
                    <option value="AL">Alabama</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>   -->              
                {!! Form::select('leads[]', $unAssignedLead, null,['multiple'=>'multiple','class' => '3col active','id'=>'leads'] ) !!}
                  @if($errors->has('leads'))
                  <div class="error">{{ $errors->first('leads') }}</div>
                  @endif
               </div>
               <!-- /.form-group -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
               <div class="form-group">
                  <label for="user_id">Select Agent <span class="mandatoryClass">*</span></label>
                  {!! Form::select('user_id', getAgentsList(), null,['class' => 'form-control select2','id'=>'user_id'] ) !!}
               </div>
               <!-- /.form-group -->
            </div>
         </div>
         <!-- /.col -->
         <button type="submit" class="btn btn-primary">Submit</button>
      </div>
      {!! Form::close() !!}
      @else
      <div class="box-header with-border">
         <h5>Oops!! No holding leads available</h5>
      </div>
      @endif
   </div>
   <!-- /.box -->
</section>
<!-- /.content -->
@section('pagescript')
<script>
   $(function () {
   	//Initialize Select2 Elements
   	$('.select2').select2();
   	$('select[multiple].active.3col').multiselect({
            columns: 2,
            placeholder: 'Select Holding Leads',
            search: true,
            searchOptions: {
                'default': 'Search Holding Leads'
            },
            selectAll: true
        });
   
   	//Form validation
   	 $("#assignLeads").validate({
   		// Specify validation rules
   		rules: {
   		  // The key name on the left side is the name attribute
   		  // of an input field. Validation rules are defined
   		  // on the right side
   		  "leads[]": "required",
   		  user_id: "required"
   		},
   		// Specify validation error messages
   		messages: {
        "leads[]": "Please select leads",
        user_id: "Please select agent"
   		},
   		// Make sure the form is submitted to the destination defined
   		// in the "action" attribute of the form when valid
   		submitHandler: function(form) {
   		  form.submit();
   		}
   	  });
   })
</script>
@stop
@endsection