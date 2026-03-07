<?php


namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class QuestionCommnent extends Model
{
    protected $table = "question_commnent";
    protected $guarded = [
        'id',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerRegister::class, 'userId', 'id');
    }
    
    // Define the relationship for child comments
    public function childComments()
    {
        return $this->hasMany(QuestionCommnent::class, 'parentId');
    }

}
