<?php


namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class UnnecessaryWords extends Model
{
    protected $table = "unnecessary_words";
    protected $guarded = [
        'id',
    ];
}
