<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;
use Laraspace\Topics;
class Exam extends Model
{
	protected $table = "exam";
	// Accessor to retrieve related topics
    public function gettopicsattribute()
    {
        // Retrieve topic IDs from the topic_id column and convert them to an array
        $topicIds = explode(',', $this->topics_id);

        // Retrieve topics based on the IDs

        $final = Topics::whereIn('id', $topicIds)->get();
        return $final ;
    }
    public function topics()
    {
        return $this->belongsToMany(Topics::class, 'examquerel', 'exam_id', 'topic_id');

    }
	
}
