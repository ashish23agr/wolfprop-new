<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
//use Corcel\Model;
//use Corcel\Model\Post;
use App\Lead;

class LeadAction extends Model
{
	protected $fillable = [
        'user_id','lead_id', 'property_id', 'action'
    ];
    public function post_detail()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'property_id');
    }
    public function client_info()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
        
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return PostBuilder
     */
    /*public function newEloquentBuilder($query)
    {
        return new PostBuilder($query);
    }*/

    /**
     * @return PostBuilder
     */
    /*public function newQuery()
    {
        return $this->postType ?
            parent::newQuery()->type($this->postType) :
            parent::newQuery();
    }*/
}

