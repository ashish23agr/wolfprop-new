@extends('layouts.admin.app')

@section('content')
<section class="content-header">
    <h1>
    Property Detail
        <small>Here you can view property detail</small>
    </h1>
    
</section>

<section class="content" data-table="emailHooks">
    <div class="box">
        <div class="box-header">
            <h3 style="text-align:center"><strong>Property Detail</strong></h3>  
            <a href="{{ URL::previous() }}" class="btn btn-default pull-right" title="Go Back"><i class="fa fa-fw fa-chevron-circle-left"></i> Back</a>
        </div>
        <div class="box-body">
            <table class="table table-hover table-striped">
                <tr>
                    <th scope="row">{{ __('Image') }}:</th>
                    <td><img src="{{@$property->image}}" alt="Image" height="50" width="100">
                    </td>
                    <th scope="row"><?= __('Title') ?>:</th>
                    <td>{!!ucwords(strtolower(@$property->post_title))!!}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Owner Name') }}:</th>
                    <td>{!!ucwords(strtolower(@$property->author->user_login))!!}</td>
                    <th scope="row">{{ __('Price') }}:</th>
                    <td>
                        <?php
                        if($property && !empty($property->meta)){
                            foreach($property->meta as $row){
                                if($row->meta_key == 'listing_price'){
                                    echo $row->value;
                                }
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">{{ __('Address') }}:</th>
                    <td><?php
                        if($property && !empty($property->meta)){
                            foreach($property->meta as $row){
                                if($row->meta_key == 'address'){
                                    echo $row->value;
                                }
                            }
                        }
                        ?></td>
                        
                    <th scope="row">{{ __('Mobile Number') }}:</th>
                    <td><?php
                        if($property && !empty($property->meta)){
                            foreach($property->meta as $row){
                                if($row->meta_key == 'landlord_contact_#'){
                                    echo @$row->value;
                                }
                            }
                        }
                        ?></td>
                </tr>
            </table>
            
        </div>
    </div>
</section>

@endsection
