<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
