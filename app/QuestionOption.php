<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
	protected $table = "tblquestionoption";
	public $timestamps = false;
	protected $primaryKey = 'questionId';
	
   
	protected $fillable = [
        'questionId','questionImageText','isCorrectAnswer'
        // Add other fillable fields here if you have any
    ];
    public function question()
    {
        return $this->belongsTo(Questions::class, 'questionId', 'questionId');
    }
}
