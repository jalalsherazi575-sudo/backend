<?php

namespace Laraspace\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



       protected $requestSegment='';
       protected  $currentMenuId=0;

       
       public static function getcurrent() {

          if ((Request::segment(2))) {
            $requestSegment=Request::segment(2);
          } 

          if ($requestSegment=='' && (Request::segment(1))) {
            $requestSegment=Request::segment(1);
          }

        
          if ($requestSegment!='') {
            $currMenuName=DB::table('tblmenu')->where('menuShortUrl',$requestSegment)->first();
            $currentMenuId=isset($currMenuName->id)?($currMenuName->id):0;    
           }

           return $currentMenuId;

       }

    public static function get_user_role($role_id) {
         $modules=DB::table('user_roles')->where('role_id',$role_id)->first();
         
         $role="";
         if ($modules) {
            $role=$modules->role_name;
            
         }
         return $role;
    }

    
    public static function getLessionLevelValue($levelId,$lessionId) {
         $assigncount=DB::table('tblassignlessionlevel')->where([['levelId','=',$levelId],['lessionId','=',$lessionId]])->count();

         return $assigncount;
    }

     public static function getLessionLevelsortOrder($levelId,$lessionId) {
         $assignsortOrder=DB::table('tblassignlessionlevel')->where([['levelId','=',$levelId],['lessionId','=',$lessionId]])->first();
         
         if ($assignsortOrder) {
            $sortOrder=isset($assignsortOrder->lessionsortOrder)?($assignsortOrder->lessionsortOrder):1;

         } else {
            $lessionortOrder=DB::table('tbllessionmanagement')->where([['lessionId','=',$lessionId]])->first();
            $sortOrder=isset($lessionortOrder->sortOrder)?($lessionortOrder->sortOrder):1;
         } 

         return $sortOrder;
    }

    public static function getQuestionCount($questionId) {
         $parent_modules=DB::table('tblpostcustomersurvey')->where([['questionId','=',$questionId]])->count();

         return $parent_modules;
    }     

    public static function getCreateModuleValue($module_id,$user_id) {
         $parent_modules=DB::table('users_permision')->where([['menu_id','=',$module_id],['role_id','=',$user_id],['is_create','=','1']])->count();

         return $parent_modules;
    }

    public static function getUpdateModuleValue($module_id,$user_id) {
         $parent_modules=DB::table('users_permision')->where([['menu_id','=',$module_id],['role_id','=',$user_id],['is_update','=','1']])->count();

         return $parent_modules;
    }

    public static function getDeleteModuleValue($module_id,$user_id) {
         $parent_modules=DB::table('users_permision')->where([['menu_id','=',$module_id],['role_id','=',$user_id],['is_delete','=','1']])->count();

         return $parent_modules;
    }

    public static function getViewModuleValue($module_id,$user_id) {
         $parent_modules=DB::table('users_permision')->where([['menu_id','=',$module_id],['role_id','=',$user_id],['is_view','=','1']])->count();

         return $parent_modules;
    }
}
