<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Corcel\Model\User as User;
class Lead extends Model
{
	protected $fillable = [
        'user_id','first_name', 'last_name', 'mobile_number','home_number','email', 'address', 'move_in_date','notes','lead_status'
	];
	protected $appends = ['agent_name','lead_status_label'];
		
	public function getAgentNameAttribute()
	{
		$agentName = '';
		if($this->user_id && !empty($this->user_id) && User::where('ID', '=', $this->user_id)->exists()){
			$user = User::where('ID', '=',$this->user_id)->select('ID','user_login')->first();
			$agentName = $user->user_login;
		}
		return $agentName;
	}
	public function getLeadStatusLabelAttribute()
	{
		$leadStatusName = !empty($this->lead_status)?getLeadStatus(true,$this->lead_status):"N/A";
		return $leadStatusName;
	}
	public function leadDetail()
    {
        return $this->hasOne(LeadDetail::class);
    }
	public function userInfo()
    {
        return $this->belongsTo(User::class,'user_id','ID');
    }
	public function leadBedrooms()
    {
        return $this->hasMany(LeadBedroom::class);
    }
	public function leadBathrooms()
    {
        return $this->hasMany(LeadBathroom::class);
    }
	public function leadParking()
    {
        return $this->hasMany(LeadParking::class);
    }
	public function leadNeighborhood()
    {
        return $this->hasMany(LeadNeighborhood::class);
    }

}

