<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ExamQueRel extends Model
{
	protected $table = "examquerel";
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_id','topic_id'
        // Other fillable attributes go here if any
    ];
}
