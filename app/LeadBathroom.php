<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Bathroom;
class LeadBathroom extends Model
{
	public $timestamps = false;
    protected $appends = ['bath_room_value'];
	public function bathroomData()
    {
        return $this->belongsTo(Bathroom::class,'bathroom_id');
    }
    public function getBathRoomValueAttribute()
	{
		$bathroomvalue = '';
		if($this->bathroom_id && !empty($this->bathroom_id) && Bathroom::where('id', '=', $this->bathroom_id)->exists()){
          
			$user = Bathroom::where('id', '=',$this->bathroom_id)->select('id','bathroom')->first();
			$bathroomvalue = $user->bathroom;
		}
		return $bathroomvalue;
	}
}

