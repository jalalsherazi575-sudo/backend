<?php
namespace Laraspace\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laraspace\Http\Controllers\CommanController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\LaraspaceClean::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('transactions:update_status')->daily();
        /*$schedule->call(function () {
            $currentDate=date("Y-m-d");
            $common=new CommanController;
             
             $vendorsubscription=DB::select( DB::raw("Select subscription.id,subscription.venderId,subscription.subscriptionPlanId,subscription.endDate,subscription.status,vendor.deviceType,vendor.deviceToken,vendor.langId,subscription.subscriptionName from tblvendersubscription as subscription INNER JOIN tblvender as vendor ON subscription.venderId=vendor.id where (subscription.endDate < '".$currentDate."' OR subscription.status=1) and vendor.isActive=1 GROUP by vendor.id"));

            if ($vendorsubscription > 0) {
                foreach ($vendorsubscription as  $value) {
                      $id=($value->id)?($value->id):0;
                      $vendorId=($value->venderId)?($value->venderId):0;
                      $subscriptionPlanId=($value->subscriptionPlanId)?($value->subscriptionPlanId):"";
                      $endDate=($value->endDate)?($value->endDate):"";
                      $status=($value->status)?($value->status):0;
                      $deviceType=($value->deviceType)?($value->deviceType):0;
                      $deviceToken=($value->deviceToken)?($value->deviceToken):"";
                      $subscriptionName=($value->subscriptionName)?($value->subscriptionName):"";

                      
                      
                      $langId=($value->langId)?($value->langId):0;

                      $vendorName=$common->vendorName($vendorId);
                      $vendorUrl=$common->vendorProfilePic($vendorId);

                      $notificationmsg=$common->get_msg("plan_expired",$langId)?$common->get_msg("plan_expired",$langId):"Your plan has been expired.please subscribe for use features.";
                        if ($notificationmsg!='') {
                            $Description=str_replace("#name",$subscriptionName,$notificationmsg);
                        } else {
                            $Description="Your plan has been expired.please subscribe for use features.";
                        }
                      
                      if ($endDate < $currentDate) {
                            $updateStatus=DB::table('tblvendersubscription')->where([['id', '=',$id]])->update(
                   ['status'=>2,'subscriptionInactiveDate'=>date("Y-m-d H:i:s")]);

                            $logs=DB::table('tblsubscriptioninactivelogs')->insert(
                          ['vendorId'=>$vendorId,'planId'=>$subscriptionPlanId,'createdDate'=>date('Y-m-d H:i:s')]);

                                         if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                    $ExtraInfo = array('notificationType'=>8,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>0,'customerId'=>0,'vendorId'=>$vendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>0,'productId'=>0,'cutomerName'=>'','cutomerUrl'=>'','vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken));  
                                                }
                                            }

                                            if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                    $ExtraInfo = array('notificationType'=>8,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>0,'customerId'=>0,'vendorId'=>$vendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>0,'productId'=>0,'cutomerName'=>'','cutomerUrl'=>'','vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken));
                                                    
                                                }
                                            }
                      }
                }
                echo "cron has been run.";
             }
        })->everyMinute();*/

        
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
