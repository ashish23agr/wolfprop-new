<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class LeadBedroom extends Model
{
    public $timestamps = false;
    protected $appends = ['bed_room_value'];
	public function bedroomData()
    {
        return $this->belongsTo(Bedroom::class,'bedroom_id');
    }

    public function getBedRoomValueAttribute()
	{
		$bathroomvalue = '';
		if($this->bedroom_id && !empty($this->bedroom_id) && Bedroom::where('id', '=', $this->bedroom_id)->exists()){
          
			$user = Bedroom::where('id', '=',$this->bedroom_id)->select('id','bedroom')->first();
			$bathroomvalue = $user->bedroom;
		}
		return $bathroomvalue;
	}
}

