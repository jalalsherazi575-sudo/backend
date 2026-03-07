<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Invoice;
use Laraspace\IPNStatus;
use Laraspace\Item;
use Laraspace\SubscriptionPlan;
use Laraspace\Vendor;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\AdaptivePayments;
use Srmklive\PayPal\Services\ExpressCheckout;
use Illuminate\Support\Facades\DB;
use Laraspace\Http\Controllers\CommanController;

class PayPalController extends Controller
{
    /**
     * @var ExpressCheckout
     */
    protected $provider;

    public function __construct()
    {
        $this->provider = new ExpressCheckout();
    }

    public function getIndex(Request $request)
    {
        $response = [];
        if (session()->has('code')) {
            $response['code'] = session()->get('code');
            session()->forget('code');
        }

        if (session()->has('message')) {
            $response['message'] = session()->get('message');
            session()->forget('message');
        }

        if (session()->has('paymentResponse')) {
            $response['paymentResponse'] = session()->get('paymentResponse');
            session()->forget('paymentResponse');
        }

        if (session()->has('responseObj')) {
            $response['responseObj'] = session()->get('responseObj');
            session()->forget('responseObj');
        }

        if (session()->has('planDetails')) {
            $response['planDetails'] = session()->get('planDetails');
            session()->forget('planDetails');
        }

        if (session()->has('profileId')) {
            $response['profileId'] = session()->get('profileId');
            session()->forget('profileId');
        }

        return view('front.paymentsuccess', compact('response'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getExpressCheckout(Request $request)
    {

        $recurring = ($request->get('mode') === 'recurring') ? true : false;
        $vendorId=($request->vendorId)?($request->vendorId):0;
        $planId=($request->planId)?($request->planId):0;
        $cart = $this->getCheckoutData($recurring,$vendorId,$planId);
        
        try {
            $response = $this->provider->setExpressCheckout($cart, $recurring);
            return redirect($response['paypal_link']);
        } catch (\Exception $e) {
            $invoice = $this->createInvoice($cart, 'Invalid');
            session()->put(['code' => 'danger', 'message' => "Error processing PayPal payment for Order $invoice->id!"]);
        }
    }

    /**
     * Process payment on PayPal.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */

    public function cancelSubscription(Request $request) {
           $common=new CommanController;
           $langId=($request->header('langId'))?($request->header('langId')):1; 
           $currentdate=date("Y-m-d");
           
           if (!$request->vendorId) {
       $myarray['result']=(object)array();          
       $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
       $myarray['status']=0;
          } else {

               $vendorId=($request->vendorId)?($request->vendorId):0;

              $ActivaPlan= DB::table('tblvendersubscription')->where([['venderId', '=',$vendorId],['endDate', '>=',$currentdate],['price','>=','0'],['status', '=',1]])->first();
                 
                 $noOfRemainingLeads=0;
                 if (!empty($ActivaPlan)) {
                    $uniId=isset($ActivaPlan->id)?($ActivaPlan->id):0;
                    $noOfRemainingLeads=isset($ActivaPlan->noOfRemainingLeads)?($ActivaPlan->noOfRemainingLeads):0;
                    $cancelprofileId=isset($ActivaPlan->profileId)?($ActivaPlan->profileId):"";
                    $cancelPlanId=isset($ActivaPlan->subscriptionPlanId)?($ActivaPlan->subscriptionPlanId):0;
                    
                    $cancelrequest = $this->provider->cancelRecurringPaymentsProfile($cancelprofileId);

                      if (isset($cancelrequest)) {
                        $cancelsub=DB::table('tblsubscriptioncancelrequest')->insertGetId(
               ['vendorId'=>$vendorId,'planId'=>$cancelPlanId,'profileId'=>$cancelprofileId,'isCancel'=>1,'createdDate'=>date('Y-m-d H:i:s'),'subscriptionId'=>$uniId]);
                     } else {
                         $cancelsub=DB::table('tblsubscriptioncancelrequest')->insertGetId(
               ['vendorId'=>$vendorId,'planId'=>$cancelPlanId,'profileId'=>$cancelprofileId,'isCancel'=>0,'createdDate'=>date('Y-m-d H:i:s'),'subscriptionId'=>$uniId]);
                     }

                     $res=array('cancelSubscriptionDate'=>date('Y-m-d H:i:s'));

                     $myarray['result']=$res;
                    $myarray['message']=$common->get_msg("plan_cancel",$langId)?$common->get_msg("plan_cancel",$langId):"Your plan has been cancelled successfully.";
                    $myarray['status']=1;

                  } else {
                    $myarray['result']=(object)array();
                    $myarray['message']=$common->get_msg("no_active_plan",$langId)?$common->get_msg("no_active_plan",$langId):"You have not any active plan for cancel subscription.";
                    $myarray['status']=0;
                  }

          }  

          return response()->json($myarray); 
    }

    public function getExpressCheckoutSuccess(Request $request)
    {
        $recurring = ($request->get('mode') === 'recurring') ? true : false;
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');
        
        $vendorId=($request->vendorId)?($request->vendorId):0;
        $planId=($request->planId)?($request->planId):0;
        $cart = $this->getCheckoutData($recurring,$vendorId,$planId);
        $response = $this->provider->getExpressCheckoutDetails($token);
        $res=json_encode($response);
         
         $planDetails=SubscriptionPlan::find($planId);
         $price=($planDetails->price)?($planDetails->price):'9.99';
         $planName=($planDetails->name)?($planDetails->name):'';
         $noOfLeadsPerDuration=($planDetails->noOfLeadsPerDuration)?($planDetails->noOfLeadsPerDuration):'';
         $planDescription=($planDetails->description)?($planDetails->description):'';

        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            if ($recurring === true) {


                $response = $this->provider->createMonthlySubscription($response['TOKEN'],$price, $cart['subscription_desc']);
                //echo $price;
                //exit();
               // print_r($response);
               // exit();
                //echo $response['TOKEN'];
                //exit;
                if (!empty($response['PROFILESTATUS']) && in_array($response['PROFILESTATUS'], ['ActiveProfile', 'PendingProfile'])) {
                    $status = 'Processed';
                } else {
                    $status = 'Invalid';
                }
            } else {
                // Perform transaction on PayPal
                $payment_status = $this->provider->doExpressCheckoutPayment($cart, $token, $PayerID);
                $status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];
            }
            
            $invoice = $this->createInvoice($cart, $status);
               //print_r($response);
                //exit();
              //echo $response['TOKEN'];
              //exit;
            if ($response) {
                $profileId=isset($response['PROFILEID'])?($response['PROFILEID']):"";
                $profileStatus=isset($response['PROFILESTATUS'])?($response['PROFILESTATUS']):"";
                $transactionDate=isset($response['TIMESTAMP'])?($response['TIMESTAMP']):"";
                $correlationId=isset($response['CORRELATIONID'])?($response['CORRELATIONID']):"";
                $ack=isset($response['ACK'])?($response['ACK']):"";
                $version=isset($response['VERSION'])?($response['VERSION']):0;
                $build=isset($response['BUILD'])?($response['BUILD']):"";
                
                 $vendorsubscription = DB::table('tblvendersubscription')->where([['venderId', '=',$vendorId],['subscriptionPlanId', '=',1]])->get();
                 if (count($vendorsubscription) > 0) {

                         $updateStatus=DB::table('tblvendersubscription')->where([['venderId', '=',$vendorId],['subscriptionPlanId', '=',1]])->update(
                   ['status'=>2,'subscriptionInactiveDate'=>date("Y-m-d H:i:s")]);   
                 }
                 
                 $currentdate=date("Y-m-d");
                 $ActivaPlan= DB::table('tblvendersubscription')->where([['venderId', '=',$vendorId],['endDate', '>=',$currentdate],['price','>=','0'],['status', '=',1]])->first();
                 
                 $noOfRemainingLeads=0;
                 if (!empty($ActivaPlan)) {
                    $uniId=isset($ActivaPlan->id)?($ActivaPlan->id):0;
                    $noOfRemainingLeads=isset($ActivaPlan->noOfRemainingLeads)?($ActivaPlan->noOfRemainingLeads):0;
                    $cancelprofileId=isset($ActivaPlan->profileId)?($ActivaPlan->profileId):"";
                    $cancelPlanId=isset($ActivaPlan->subscriptionPlanId)?($ActivaPlan->subscriptionPlanId):0;

                    $updateStatus=DB::table('tblvendersubscription')->where([['id', '=',$uniId]])->update(
                   ['status'=>2,'subscriptionInactiveDate'=>date("Y-m-d H:i:s")]);

                    $cancelrequest = $this->provider->cancelRecurringPaymentsProfile($cancelprofileId);
                     if (isset($cancelrequest)) {
                        $cancelsub=DB::table('tblsubscriptioncancelrequest')->insertGetId(
               ['vendorId'=>$vendorId,'planId'=>$cancelPlanId,'profileId'=>$cancelprofileId,'isCancel'=>1,'createdDate'=>date('Y-m-d H:i:s'),'subscriptionId'=>$uniId]);
                     } else {
                         $cancelsub=DB::table('tblsubscriptioncancelrequest')->insertGetId(
               ['vendorId'=>$vendorId,'planId'=>$cancelPlanId,'profileId'=>$cancelprofileId,'isCancel'=>0,'createdDate'=>date('Y-m-d H:i:s'),'subscriptionId'=>$uniId]);
                     }
                    
                 }

                $insertEntry=DB::table('tblvendortranscation')->insertGetId(
               ['profileId'=>$profileId,'profileStatus'=>$profileStatus,'transactionDate'=>$transactionDate,'createdDate'=>date('Y-m-d H:i:s'),'correlationId'=>$correlationId,'ack'=>$ack,'version'=>$version,'build'=>$build,'vendorId'=>$vendorId,'planId'=>$planId,'planPrice'=>$price,'invoiceId'=>$invoice->id,'paid'=>$invoice->paid]);
                $startDate=date("Y-m-d");
                $endDate=date("Y-m-d",strtotime('+1 month'));
                $totalLeads=$noOfRemainingLeads+$noOfLeadsPerDuration;
                $subID=DB::table('tblvendersubscription')->insertGetId(
               ['venderId'=>$vendorId,'subscriptionPlanId'=>$planId,'subscriptionName'=>$planName,'subscriptionDesc'=>$planDescription,'price'=>$price,'startDate'=>$startDate,'endDate'=>$endDate,'noOfLeadsPerDuration'=>$totalLeads,'noOfRemainingLeads'=>$totalLeads,'status'=>1,'createdDate'=>date('Y-m-d H:i:s'),'vendorTranscationId'=>$insertEntry,'profileId'=>$profileId]);
                $history=DB::table('tblvendersubscriptionhistory')->insertGetId(
               ['subscriptionId'=>$subID,'startDate'=>$startDate,'endDate'=>$endDate,'paymentStatus'=>'Success','paymentResponse'=>$res]);
            }
              

            if ($invoice->paid) {
                session()->put(['code' => 'success', 'message' => "Order $invoice->id has been paid successfully!",'paymentResponse'=>$res,'responseObj'=>$response,'planDetails'=>$planDetails,'profileId'=>$profileId]);
                flash()->success('Order '.$invoice->id.' has been paid successfully!');
                return redirect()->to('payment/success?success');
            } else {
                flash()->error('Error processing PayPal payment for Order '.$invoice->id.'!');
                return redirect()->to('payment/cancel');
                session()->put(['code' => 'danger', 'message' => "Error processing PayPal payment for Order $invoice->id!"]);
            }

            //return redirect('/');
        }
    }

    public function getAdaptivePay()
    {
        $this->provider = new AdaptivePayments();

        $data = [
            'receivers'  => [
                [
                    'email'   => 'johndoe@example.com',
                    'amount'  => 10,
                    'primary' => true,
                ],
                [
                    'email'   => 'janedoe@example.com',
                    'amount'  => 5,
                    'primary' => false,
                ],
            ],
            'payer'      => 'EACHRECEIVER', // (Optional) Describes who pays PayPal fees. Allowed values are: 'SENDER', 'PRIMARYRECEIVER', 'EACHRECEIVER' (Default), 'SECONDARYONLY'
            'return_url' => url('payment/success?success'),
            'cancel_url' => url('payment/cancel'),
        ];

        $response = $this->provider->createPayRequest($data);
        //dd($response);
    }

    /**
     * Parse PayPal IPN.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function notify(Request $request)
    {
        if (!($this->provider instanceof ExpressCheckout)) {
            $this->provider = new ExpressCheckout();
        }

        $post = [
            'cmd' => '_notify-validate',
        ];
        $data = $request->all();
        foreach ($data as $key => $value) {
            $post[$key] = $value;
        }

        $response = (string) $this->provider->verifyIPN($post);

        $ipn = new IPNStatus();
        $ipn->payload = json_encode($post);
        $ipn->status = $response;
        $ipn->save();
    }

    /**
     * Set cart data for processing payment on PayPal.
     *
     * @param bool $recurring
     *
     * @return array
     */
    protected function getCheckoutData($recurring = false,$vendorId=0,$planId=0)
    {
        $data = [];
           
         $planDetails=SubscriptionPlan::find($planId);
         $planname=($planDetails->name)?($planDetails->name):"";  
         $price=($planDetails->price)?($planDetails->price):'0';
         $order_id = Invoice::all()->count() + 14;
        //echo $order_id;
        // exit;
        if ($recurring === true) {
            $data['items'] = [
                [
                    'name'  => $planname.' '.config('paypal.invoice_prefix').' #'.$order_id,
                    'price' => $price,
                    'qty'   => 1,
                ],
            ];

            $data['return_url'] = url('/paypal/ec-checkout-success?mode=recurring&vendorId='.$vendorId.'&planId='.$planId);
            $data['subscription_desc'] = $planname.' '.config('paypal.invoice_prefix').' #'.$order_id;
        } else {
            $data['items'] = [
                [
                    'name'  => 'Product 1',
                    'price' => 9.99,
                    'qty'   => 1,
                ],
                [
                    'name'  => 'Product 2',
                    'price' => 4.99,
                    'qty'   => 2,
                ],
            ];

            $data['return_url'] = url('/paypal/ec-checkout-success');
        }

        $rand=rand(1,100000);
        //echo $rand;
        //exit();
        $data['invoice_id'] = config('paypal.invoice_prefix').'_'.$rand;
        $data['invoice_description'] = "Order #$order_id Invoice";
        $data['cancel_url'] = url('payment/cancel');

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $data['total'] = $total;
          //print_r($data);
          //exit();
        return $data;
    }

    /**
     * Create invoice.
     *
     * @param array  $cart
     * @param string $status
     *
     * @return \App\Invoice
     */
    protected function createInvoice($cart, $status)
    {
        $invoice = new Invoice();
        $invoice->title = $cart['invoice_description'];
        $invoice->price = $cart['total'];
        if (!strcasecmp($status, 'Completed') || !strcasecmp($status, 'Processed')) {
            $invoice->paid = 1;
        } else {
            $invoice->paid = 0;
        }
        $invoice->save();

        collect($cart['items'])->each(function ($product) use ($invoice) {
            $item = new Item();
            $item->invoice_id = $invoice->id;
            $item->item_name = $product['name'];
            $item->item_price = $product['price'];
            $item->item_qty = $product['qty'];

            $item->save();
        });

        return $invoice;
    }
}
