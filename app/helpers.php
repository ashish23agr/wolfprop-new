<?php

use Corcel\Model\User as User;

use Corcel\Model\Taxonomy as Taxonomy;

use App\Bedroom;

use App\Bathroom;

if (! function_exists('getAgentsList')) {

    function getAgentsList() {

        $agentArr = collect([]);

		$agents = User::whereHas('meta', function($query){

			$query->where('meta_key', 'role');

			$query->where('meta_value', 'member');

		})->selectRaw('ID, user_login,user_email')->get();

		foreach($agents as $agent){

			$agentArr->offsetSet($agent->ID, $agent->user_login." (".$agent->user_email.")");

		}

        $agentArr->prepend('Please select agent','');

        return $agentArr;

    }

}

if (! function_exists('getLeadStatus')) {

    function getLeadStatus($label=false,$index=null) {

        $leadStatus = array(

			''=>'Select status',

			'1'=>'Completed',

			'2'=>'Backup Lead',

			'3'=>'Hot',

			'4'=>'Cold',

			'5'=>'Followup Date',

                        '6'=>'Active'

		);

		if($label==true){

			return $leadStatus[$index];

		}

        return $leadStatus;

    }

}

if (! function_exists('getStates')) {

    function getStates() {

		// get state list

		$states = collect([]);

		$statelists = Taxonomy::where('taxonomy', 'state')->get();

		foreach($statelists as $statelist){

		  $states->offsetSet($statelist->term_taxonomy_id, ucwords(strtolower($statelist->term->name)));

		}

		return $states;

    }

}

if (! function_exists('getNeighbourhood')) {

    function getNeighbourhood($label=false) {

		// get neighbourhood

		$neighbourhood = collect([]);

		$neighbourhoodlists = Taxonomy::where('taxonomy', 'neighbourhood')->get();

		foreach($neighbourhoodlists as $neighbourhoodlist){

		  $neighbourhood->offsetSet($neighbourhoodlist->term_taxonomy_id, ucwords(strtolower($neighbourhoodlist->term->name)));

		}

		if($label==true){

			$neighbourhood->prepend('Select Neighbourhood', '');

		}

		return $neighbourhood;

    }

}

if (! function_exists('getBedrooms')) {

    function getBedrooms($label=false) {

		// get bedrooms list

		$bedrooms = collect([]);

		$bedroomslists = Bedroom::get();

		$str = " Bedroom";

		foreach($bedroomslists as $k=> $list){

			if($k > 0)

			$str = " Bedrooms";

			$bedrooms->offsetSet($list->id, $list->bedroom.$str);

			if($label==true){

				$bedrooms->offsetSet($list->bedroom, $list->bedroom.$str);

			}

		}

		if($label==true){

			$bedrooms->prepend('Select Bedroom', '');

		}

		return $bedrooms;

    }

}

if (! function_exists('getBathrooms')) {

    function getBathrooms($label=false) {

		// get bathrooms list

		$bathrooms = collect([]);

		$bathroomslists = Bathroom::get();

		

		$str = " Bathroom";

		foreach($bathroomslists as $k=> $list){

			if($k > 0)

			$str = " Bathrooms";

			$bathrooms->offsetSet($list->id, $list->bathroom.$str);

		}

		if($label==true){

			$bathrooms->prepend('Select Bathroom', '');

		}

		return $bathrooms;



    }

}

if (! function_exists('getParking')) {
    function getParking($label=false) {
		// get getParkingLists
		$getParkingLists = collect([]);
		$getParkingListsResult = Taxonomy::where('taxonomy', 'parking')->orderBy('term_taxonomy_id', 'asc')->get();
		// echo "<pre>"; print_r($getParkingListsResult->toArray()); exit;
		foreach($getParkingListsResult as $getParkingRow){
			if(isset($getParkingRow->term) && !empty($getParkingRow->term))
			{
			  $getParkingLists->offsetSet($getParkingRow->term->slug, ucwords(strtolower($getParkingRow->term->name)));
			}	

		}
		if($label==true){
			$getParkingLists->prepend('Select Paking', '');
		}
		return $getParkingLists;
    }
}

