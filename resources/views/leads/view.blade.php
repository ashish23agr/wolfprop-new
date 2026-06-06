@extends('layouts.admin.app')

@section('content')
<?php
    use Corcel\Model\Taxonomy as Taxonomy;
    if(@$leadDetail->leadDetail && $leadDetail->leadDetail->state_id && !empty($leadDetail->leadDetail->state_id) && Taxonomy::where('term_taxonomy_id', '=', $leadDetail->leadDetail->state_id)->exists()){
        $state = Taxonomy::where('term_taxonomy_id', $leadDetail->leadDetail->state_id)->first();
    }
    if(@$leadDetail->leadDetail && $leadDetail->leadDetail->neighborhood && !empty($leadDetail->leadDetail->neighborhood) && Taxonomy::where('term_taxonomy_id', '=', $leadDetail->leadDetail->neighborhood)->exists()){
        $neighborhood = Taxonomy::where('term_taxonomy_id', $leadDetail->leadDetail->neighborhood)->first();
    }
?>
<section class="content-header">
    <h1>
    Lead Detail
        <small>Here you can view lead detail</small>
    </h1>
    
</section>

<section class="content" data-table="emailHooks">
    <div class="box">
        <div class="box-header">
            <h3 style="text-align:center"><strong>Personal Information</strong></h3>  
            <a href="{{ URL::previous() }}" class="btn btn-default pull-right" title="Cancel"><i class="fa fa-fw fa-chevron-circle-left"></i> Back</a>
        </div>
        <div class="box-body">
            <table class="table table-hover table-striped">
                <tr>
                    <th scope="row"><?= __('Client Name') ?>:</th>
                    <td>{{ucwords(strtolower($leadDetail->first_name.' '.$leadDetail->last_name))}}</td>
                    @if(Auth::user()->role == 'admin')
                    <th scope="row">{{ __('Assigned Agent') }}:</th>
                    <td>{{ucwords(strtolower($leadDetail->agent_name))}}</td>
                    @else
                    <th scope="row">{{ __('Notes') }}:</th>
                    <td>{{$leadDetail->notes}}</td>
                    @endif
                </tr>
                <tr>
                    <th scope="row">{{ __('Email') }}:</th>
                    <td>{{$leadDetail->email}}</td>
                    <th scope="row">{{ __('Mobile Number') }}:</th>
                    <td>{{$leadDetail->mobile_number}}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Home Number') }}:</th>
                    <td>{{$leadDetail->home_number}}</td>
                    <th scope="row">{{ __('Address') }}:</th>
                    <td>{{$leadDetail->address}}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Lead Current Status') }}:</th>
                    <td><?php $leadStatusName = getLeadStatus(true,$leadDetail->lead_status); echo $leadStatusName;?></td>
                    <th scope="row">{{ __('Lead Status') }}:</th>
                    <td><?php if($leadDetail->status == 0) echo 'Inactive'; else echo 'Active';?></td>
                </tr>
                <tr>
                    <!--<th scope="row">{{ __('Move In Date') }}:</th>
                    <td>{{$leadDetail->move_in_date}}</td>-->
                    <th scope="row">{{ __('Created On') }}:</th>
                    <td>{{$leadDetail->created_at}}</td>
                </tr>
                @if(Auth::user()->role == 'admin')
                <tr>
                    <th scope="row">{{ __('Notes') }}:</th>
                    <td>{{$leadDetail->notes}}</td>
                </tr>
                @endif
            </table>
            
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h3 style="text-align:center"><strong>Lead Information</strong></h3>  
        </div>
        <div class="box-body">
            <table class="table table-hover table-striped">
                
                <tr>
                    <th scope="row">{{ __('Category') }}:</th>
                    <td>{{ucwords(strtolower(@$leadDetail->leadDetail->category))}}</td>
                    <th scope="row">{{ __('No. of Bedbroom') }}:</th>
                    <td>
                    
                    <?php 
                    if(!empty($leadDetail->leadBedrooms) && count($leadDetail->leadBedrooms) > 0 ){
                        $str = '';
                        foreach ($leadDetail->leadBedrooms as $bed) {
                            $str .= $bed->bed_room_value . ",";
                        }
                        echo trim($str, ',').' Bedroom(s)';
 }else {echo 'N/A';}?></td>
                </tr>
                <tr>
                    <th scope="row">{{ __('No. of Bathroom') }}:</th>
                    <td><?php 
                    if(!empty($leadDetail->leadBathrooms) && count($leadDetail->leadBathrooms) > 0){
                        $str = '';
                        foreach ($leadDetail->leadBathrooms as $bed) {
                            $str .= $bed->bath_room_value . ",";
                        }
                        echo trim($str, ',').' Bathroom(s)';
 }else{ echo 'N/A';}?></td>
                    <th scope="row">{{ __('Parking') }}:</th>
                    <td><?php 
                    if(!empty($leadDetail->leadParking) && count($leadDetail->leadParking) > 0){
                        $str = '';
                        foreach ($leadDetail->leadParking as $bed) {
                            $str .= $bed->parking . ",";
                        }
                        echo trim(ucwords(str_replace('_',' ',$str)), ' ,');
 }else{ echo 'N/A';}?></td>


                </tr>
                <tr>
                    <th scope="row">{{ __('Minimum Budget') }}:</th>
                    <td>{{@$leadDetail->leadDetail->min_budget}}</td>
                    <th scope="row">{{ __('Maximum Budget') }}:</th>
                    <td>{{@$leadDetail->leadDetail->max_budget}}</td>
                </tr>
                <tr>
                    <!--<th scope="row">{{ __('Laundry') }}:</th>
                    <td>{{@$leadDetail->leadDetail->laundry}}</td>-->
                    <th scope="row">{{ __('Pet') }}:</th>
                    <td>{{@$leadDetail->leadDetail->pet}}</td>
                    <th scope="row">{{ __('Neighborhood') }}:</th>
                    <td>{{@$neighborhood ->term->name}}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Address Line1') }}:</th>
                    <td>{{@$leadDetail->leadDetail->address_line1}}</td>
                    <th scope="row">{{ __('Address Line2') }}:</th>
                    <td>{{@$leadDetail->leadDetail->address_line2}}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Zip Code') }}:</th>
                    <td>{{@$leadDetail->leadDetail->zipcode}}</td>
                    
                    <!--<th scope="row">{{ __('City') }}:</th>
                    <td>{{@$leadDetail->leadDetail->city}}</td>-->
                </tr>
<!--                <tr>
                    <th scope="row">{{ __('State') }}:</th>
                    <td>{{@$state->term->name}}</td>
                    <th scope="row">{{ __('Zip Code') }}:</th>
                    <td>{{@$leadDetail->leadDetail->zipcode}}</td>
                </tr>-->
            </table>
            
        </div>
    </div>
</section>

@endsection
