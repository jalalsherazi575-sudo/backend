<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Topics extends Model
{
	protected $table = "topics";
	public $timestamps = false;
	
	public function questions()
	{
	    return $this->belongsToMany(Question::class, 'topicQueRel', 'topicId', 'questionId');
	}
	 public function exams()
    {
        return $this->hasMany(Exam::class);
    }
     // Define relationships if any
    public function learnings()
    {
        return $this->hasMany(Learning::class);
    }
     public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectId', 'id');
    }
}
