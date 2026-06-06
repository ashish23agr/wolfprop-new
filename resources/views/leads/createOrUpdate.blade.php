@extends('layouts.admin.app')

@section('content')
<?php
  use Corcel\Model\Taxonomy as Taxonomy;
   
    
?>
    <section class="content-header">
      <h1>
      @if(isset($id) && !empty($id)) Update @else New @endif Lead 
        <small>Here you can @if(isset($id) && !empty($id)) update @else create new @endif lead</small>
      </h1>
      <ol class="breadcrumb">
        <li class="active"><a href="{{route('leads.index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">@if(isset($id) && !empty($id)) Update @else Add new @endif lead</li>
      </ol>
    </section>
    
    <!-- Main content -->
    <section class="content">
      <div class="box box-default">
            @if(isset($id) && !empty($id))
            {{ Form::model(@$leadDetail, ['route' => ['leads.update', @$leadDetail->id], 'method' => 'patch']) }}
            @else
            {!! Form::open(['route'=>'leads.store','id'=>'createLead','name'=>'createLead']) !!}
            @endif
            <!-- /.Basic Information -->
            <div class="box-body">
              <h3 class="box-title"><u>Personal Information</u></h3>
                <div class="row">
                  <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::hidden('lead_detail_id', @$leadDetail->leadDetail->id) !!}
                        <label for="first_name">First Name<span class="mandatoryClass">*</span></label>
                        {!! Form::text('first_name', old('first_name', @$leadDetail->first_name),['required'=>"required",'placeholder'=>"Enter first name",'class' => 'form-control','id'=>'first_name'] ) !!}                                                  
                        @if($errors->has('first_name'))
                        <div class="error">{{ $errors->first('first_name') }}</div>
                        @endif
                      </div>
                      <!-- /.form-group -->
                  </div>
                  <div class="col-md-3">
                      <div class="form-group">
                        <label for="last_name">Last Name</label>
                        {!! Form::text('last_name', old('last_name', @$leadDetail->last_name),['placeholder'=>"Enter last name",'class' => 'form-control','id'=>'last_name'] ) !!}                                                  
                      </div>
                  </div>
                  <div class="col-md-3">
                      <div class="form-group">
                        <label for="email">Email</label>
                        {!! Form::email('email', old('email', @$leadDetail->email),['placeholder'=>"Enter email address", 'class' => 'form-control','id'=>'email'] ) !!}                                                  
                        @if($errors->has('email'))
                        <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                      </div>
                  </div>
                      <div class="col-md-3">
                      <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        {!! Form::text('mobile_number', old('mobile_number', @$leadDetail->mobile_number),['placeholder'=>"Mobile number",'class' => 'form-control','id'=>'mobile_number'] ) !!}                                                  
                      </div>
                  </div>
                </div>
             <!--    <div class="row">
                
                 <div class="col-md-4">
                      <div class="form-group">
                        <label for="home_number">Home Number</label>
                        {!! Form::text('home_number', old('home_number', @$leadDetail->home_number),['placeholder'=>"Home number",'class' => 'form-control','id'=>'home_number'] ) !!}                                                  
                      </div>
                  </div>
                  
                </div>-->
                <div class="row">
                
                  <div class="col-md-3">
                      <div class="form-group">
                        <label for="address">Address</label>
                        {!! Form::text('address', old('address', @$leadDetail->address),['placeholder'=>"Enter address",'class' => 'form-control','id'=>'address'] ) !!}                                  
                      </div>
                  </div>
                  <!--<div class="col-md-3">
                      <div class="form-group">
                        <label for="move_in_date">Move In Date<span class="mandatoryClass">*</span></label>
                        <div class="input-group date">
                          <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                          </div>
                          {!! Form::text('move_in_date', old('move_in_date', @$leadDetail->move_in_date),['readonly'=>"readonly",'class' => 'form-control pull-right','id'=>'move_in_date'] ) !!}                  
                          @if($errors->has('move_in_date'))
                            <div class="error">{{ $errors->first('move_in_date') }}</div>
                          @endif
                        </div>
                      </div>
                  </div>-->
                  @if(isset($id) && !empty($id))
                  <div class="col-md-3">
                      <div class="form-group">
                          <label for="lead_status">Lead Status</label>
                          {!! Form::select('lead_status', getLeadStatus(), @$leadDetail->lead_status,['class' => 'form-control','id'=>'lead_status'] ) !!}
                      </div>
                  </div>
                  @endif 
                  @if(Auth::user()->role == 'admin')
                <div class="col-md-4">
                      <div class="form-group">
                        <label for="user_id">Assign To Agent</label>
                        {!! Form::select('user_id', getAgentsList(), @$leadDetail->user_id,['class' => 'select2 form-control','id'=>'user_id'] ) !!}
                      </div>
                  </div>
                  @endif
                </div>
                <div class="row">
                <div class="col-md-12">
                      <div class="form-group">
                        <label for="notes">Notes</label>
                        {!! Form::textarea('notes', old('notes', @$leadDetail->notes), ['class'=>"form-control",'id' => 'notes', 'rows' => 3,'placeholder'=>"Enter notes..."]) !!}                            
                      </div>
                  </div>
                  
                </div>
            </div>
            <hr>
            <!-- /.Basic Information -->
            <div class="box-body">
              <h3 class="box-title"><u>Lead Information</u></h3>
              <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="category">Category <span class="mandatoryClass">*</span></label>
                      {!! Form::select('category', ['sale-listing'=>'Sale','rental-listing'=>'Rental'], @$leadDetail->leadDetail->category,['class' => 'form-control','id'=>'category','style'=>"width: 100%;"] ) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="budget_range">Min. Budget</label>
                      {!! Form::number('min_budget', @$leadDetail->leadDetail->min_budget,['placeholder'=>'Min Budget','min'=>0,'class' => 'form-control allow_numeric','id'=>'min_budget'] ) !!}                  
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="budget_range">Max. Budget</label>
                      {!! Form::number('max_budget', @$leadDetail->leadDetail->max_budget,['placeholder'=>'Max Budget','min'=>1,'class' => 'form-control allow_numeric','id'=>'max_budget'] ) !!}                  
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="pet">Pet</label>
                      {!! Form::select('pet', [''=>'Select Pet','None'=>'None','Any'=>'Any','Cats Only'=>'Cats Only','Dogs Only'=>'Dogs Only'], @$leadDetail->leadDetail->pet,['class' => 'form-control','id'=>'pet','style'=>"width: 100%;"] ) !!}
                    </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="no_of_bedroom">Bedroom <span class="mandatoryClass">*</span></label>
                      {!! Form::select('no_of_bedroom[]', getBedrooms(), @$bedroom_ids,['multiple'=>'multiple','class' => 'form-control select-bedrooms'] ) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="no_of_bathroom">Bathroom <span class="mandatoryClass">*</span></label>
                      {!! Form::select('no_of_bathroom[]', getBathrooms(), @$bathroom_ids,['multiple'=>'multiple','class' => 'form-control select-bathrooms','id'=>'no_of_bathroom','style'=>"width: 100%;"] ) !!}                  
                    </div>
                </div>
                <!--<div class="col-md-3">
                    <div class="form-group">
                      <label for="laundry">Laundry <span class="mandatoryClass">*</span></label>
                      {!! Form::select('laundry', ['Near by'=>'Near by', 'In Unit'=>'In Unit', 'In Building'=>'In Building'], @$leadDetail->leadDetail->laundry,['required'=>"required",'class' => 'form-control','id'=>'laundry'] ) !!}
                    </div>
                </div>-->
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="parking">Parking</label>
                      {!! Form::select('parking[]', getParking(), $parking_ids,['multiple'=>'multiple','class' => 'form-control select-parking','id'=>'parking','style'=>"width: 100%;"] ) !!}
                    </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="neighborhood">Neighborhood</label>
                      {!! Form::select('neighborhood[]', getNeighbourhood(),@$neighborhood_ids,['multiple'=>'multiple','class' => 'select-neighbourhood form-control','id'=>'neighborhood'] ) !!}                                    
                    </div>
                </div>
                <!--<div class="col-md-3">
                    <div class="form-group">
                      <label for="city">City</label>
                      {!! Form::text('city', @$leadDetail->leadDetail->city,['placeholder'=>'Enter city','class' => 'form-control','id'=>'city'] ) !!}                  
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="state_id">State <span class="mandatoryClass">*</span></label>
                      {!! Form::select('state_id', getStates(), @$leadDetail->leadDetail->state_id,['required'=>"required",'class' => 'select2 form-control','id'=>'state_id'] ) !!}
                    </div>
                </div>-->
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="zipcode">Zip Code</label>
                      {!! Form::text('zipcode', old('zipcode', @$leadDetail->leadDetail->zipcode),['placeholder'=>'Zipcode','class' => 'form-control','id'=>'zipcode'] ) !!}                  
                    </div>
                </div>
              </div>
              <div class="row" id="sale-listing-div">
<!--              <div class="col-md-3">
                    <div class="form-group">
                      <label for="open_house">Open House</label>
                      {!! Form::select('open_house', [''=>'Select Status','0'=>'No','1'=>'Yes'], @$leadDetail->leadDetail->open_house,['class' => 'form-control','id'=>'open_house'] ) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="parking">Sold / under contract</label>
                      {!! Form::select('sold_under_contract', [''=>'Select Status','1'=>'Sold','2'=>'In Contract'], @$leadDetail->leadDetail->sold_under_contract,['class' => 'form-control','id'=>'sold_under_contract'] ) !!}
                    </div>
                </div>-->
              </div>
              <div class="box-footer">
                <button type="submit" class="btn btn-primary @if(Auth::user()->role != 'admin')custom-btn @endif">Submit</button>
              </div> 
          </div> 
          {!! Form::close() !!}
      </div> 
    </section>
	@section('pagescript')
  <style>
  .ms-options-wrap > .ms-options{
    width:90% !important;
  }
  </style>
		<script>
		$(function () {
    if($('#category').val() == 'sale-listing') {
        $('#sale-listing-div').show(); 
    }else{
        $('#sale-listing-div').hide(); 
    }
    $('#category').change(function(){
        if($('#category').val() == 'sale-listing') {
            $('#sale-listing-div').show(); 
        } else {
            $('#sale-listing-div').hide(); 
        } 
    });
			//Initialize Select2 Elements
			$('.select2').select2();
      $('.select-parking').multiselect({
        selectAll: true,
        placeholder: 'Select Parking'
      });
      $('.select-bedrooms').multiselect({
        selectAll: true,
        placeholder: 'Select Bedroom(s)'
      });
      $('.select-bathrooms').multiselect({
        selectAll: true,
        placeholder: 'Select Bathroom(s)'
      });
      $('.select-neighbourhood').multiselect({
        selectAll: true,
        placeholder: 'Select Neighborhood',
        search: true
      });
		/*	$('#move_in_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
      }); */
      
			//Form validation
			 $("#createLead").validate({
				// Specify validation rules
				rules: {
				  // The key name on the left side is the name attribute
				  // of an input field. Validation rules are defined
				  // on the right side
				  first_name: "required"
				  /*move_in_date: "required"
				  email: {
					required: true,
					email: true
				  }*/
				},
				// Specify validation error messages
				messages: {
				  first_name: "Please enter your firstname"
				  //move_in_date: "Please select move in date",
				  //email: "Please enter a valid email address"
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
			
