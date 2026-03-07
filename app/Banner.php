<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
	protected $table = "bannermaster";
	public $timestamps = false;
	protected $appends = ['banner_image_url'];
	public function getBannerImageUrlAttribute()
    {
        if($this->bannerImage == ""){
            return "";
        }else{
            return asset('images/banner/'.$this->bannerImage);
        }
    }
}
