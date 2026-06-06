<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class LeadDetail extends Model
{
	protected $fillable = [
        'lead_id','category', 'min_budget', 'max_budget', 'parking', 'laundry','pet','open_house','sold_under_contract','city','state_id','zipcode'
    ];
 
}

