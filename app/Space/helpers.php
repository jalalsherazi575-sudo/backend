<?php
use Laraspace\Space\Settings\Setting;
use Illuminate\Support\Facades\DB;

function set_active($path, $active = 'active')
{
    return call_user_func_array('Request::is', (array)$path) ? $active : '';
}

function is_url($path)
{
    return call_user_func_array('Request::is', (array)$path);
}

function clean_slug($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return \Illuminate\Support\Str::lower(preg_replace('/[^A-Za-z0-9\-]/', '', $string)); // Removes special chars.
}

function checkPermission($user_id,$type,$menu_id)
{

  if($type=='view') {
    $field='is_view';
  } else if ($type=='delete') {
    $field='is_delete';
  } else if ($type=='update') {
    $field='is_update';
  } else if ($type=='create') {
         $field='is_create';
    } else {
      $field='is_view';
    }
    
    

   $check=DB::select( DB::raw("select permision.$field as pem from users_permision as permision
INNER JOIN `user_roles` On permision.role_id=user_roles.role_id
INNER JOIN `users` On permision.role_id=users.role_id
INNER JOIN `tblmenu` On permision.menu_id=tblmenu.id
where `users`.id='$user_id' and permision.menu_id='$menu_id'"));
   if ($check) {
      foreach ($check as  $value) {
        $pem=$value->pem;
        if ($pem==0) {
          return false;
        } else {
          return true;
        }

      }
   } else {
    return false;
   }

}

function getSettingValue($key) {
    $SettingValue = DB::table('settings')->where([['option', '=', $key]])->first();
    $value='';
    if ($SettingValue) {
    $value=$SettingValue->value;
    }
    
    return $value;
  }

function get_setting($key)
{
    //return Setting::getSetting($key);
}
