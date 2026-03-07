<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class TopicQueRel extends Model
{
	protected $table = "topicQueRel";
	//public $timestamps = false;

	  protected $fillable = [
        'topicId','questionId'
        // Add other fillable fields here if you have any
    ];
	public function topic()
    {
        return $this->belongsTo(Topics::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
