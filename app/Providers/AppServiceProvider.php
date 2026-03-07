<?php
namespace Laraspace\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Request;
use Laravel\Passport\Passport;
use Laraspace\QuestionCommnent;
use Laraspace\Observers\CommentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
            QuestionCommnent::observe(CommentObserver::class);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreRoutes();
        //

         $requestSegment='';
        $currentMenuId=0;
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
        


        $menu = DB::table('tblmenu')->where('isActive',1)->where('isParent',1)->orderby('sortOrder','asc')->get();
        
        
        $menuList=array();

        if ($menu) {
           foreach($menu as $data) {

             $menuId=isset($data->id)?($data->id):0;
             $menuName=isset($data->menuName)?($data->menuName):0;
             
             $menuUrl=isset($data->menuUrl)?($data->menuUrl):0;
             $menuShortUrl=isset($data->menuShortUrl)?($data->menuShortUrl):0;
             $menuImage=isset($data->menuImage)?($data->menuImage):0;

             $childMenu =DB::table('tblmenu')->where('isActive',1)->where('parentId',$menuId)->orderby('menuName','asc')->get();
             $childlist=array();
             $isChild=0;
             if ($childMenu) {
                $isChild=1;
                foreach($childMenu as $chlList) {
                      $childmenuId=isset($chlList->id)?($chlList->id):0;
                      $childmenuName=isset($chlList->menuName)?($chlList->menuName):0;
                      $childmenuUrl=isset($chlList->menuUrl)?($chlList->menuUrl):0;
                      $childmenuShortUrl=isset($chlList->menuShortUrl)?($chlList->menuShortUrl):0;
                      $childmenuImage=isset($chlList->menuImage)?($chlList->menuImage):0;
                     
                     $childlist[]=array("menuId"=>$childmenuId,"menuName"=>$childmenuName,"menuUrl"=>$childmenuUrl,"menuShortUrl"=>$childmenuShortUrl,"menuImage"=>$childmenuImage);
                }
             }

             $menuList[]=array("menuId"=>$data->id,"menuName"=>$menuName,"childlist"=>$childlist,"isChild"=>$isChild,"menuUrl"=>$menuUrl,"menuShortUrl"=>$menuShortUrl,"menuImage"=>$menuImage);

           }
        }

        $this->business_users_id=1;
        $this->area_of_interest_id=2;
        $this->level_management_id=3;
        $this->customer_list_id=4;
        $this->bank_management_id=5;
        $this->id_proof_id=6;
        $this->country_management_id=7;
        $this->state_management_id=8;
        $this->city_management_id=9;
        $this->userroles_id=10;
        $this->admin_user_id=11;
        $this->mission_management_id=12;
        $this->system_settings_id=13;
        $this->general_message_id=14;
        $this->version_management_id=15;
        $this->user_info_id=19;
        
        View::share('userroles_id', $this->userroles_id);
        View::share('business_users_id', $this->business_users_id);
        View::share('area_of_interest_id', $this->area_of_interest_id);
        View::share('level_management_id', $this->level_management_id);
        View::share('customer_list_id', $this->customer_list_id);
        View::share('bank_management_id', $this->bank_management_id);
        View::share('id_proof_id', $this->id_proof_id);
        View::share('country_management_id', $this->country_management_id);
        View::share('state_management_id', $this->state_management_id);
        View::share('city_management_id', $this->city_management_id);
        View::share('admin_user_id', $this->admin_user_id);
        View::share('mission_management_id', $this->mission_management_id);
        View::share('system_settings_id', $this->system_settings_id);
        View::share('general_message_id', $this->general_message_id);
        View::share('version_management_id', $this->version_management_id);
        View::share('user_info_id', $this->user_info_id);
        View::share('menuLists', $menuList);
        View::share('currentMenuId', $currentMenuId);
    }
}
