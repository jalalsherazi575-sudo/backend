<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
	protected $table = "tblquestion";
	public $timestamps = false;
	protected $primaryKey = 'questionId';
	
	protected $fillable = ['question','description','sortOrder','isActive'];
    //

    public function topics()
		{
		    return $this->belongsToMany(Topics::class, 'topicQueRel', 'questionId', 'topicId');
		}

	/*Option*/
	public function options()
    {
        return $this->hasMany(QuestionOption::class, 'questionId', 'questionId');
    }

     public function topicQueRel()
    {
        return $this->hasMany(TopicQueRel::class, 'questionId');
    }
}
