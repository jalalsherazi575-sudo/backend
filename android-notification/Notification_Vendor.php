<?php
#API access key from Google API's Console
    if (!defined('API_ACCESS_KEY')) define( 'API_ACCESS_KEY', 'AAAAtTU5UwA:APA91bExYuVR9Vrlnnq2h_upOGo2CymzzU6kSV2CGbg2iciJueXKOMKtQrG5f0E2Bye3saJmJ2YcTQ9US8yN9eoFCmKCSyrP5eJGQp9SERR3P_3cm4kOSuuF-6usu_HrLy1alBbVUt8t' );
   // $registrationIds = 'feGSs5k1hkY:APA91bGwSQmXk7tcWE5Vm5PlB3LtsCr7J62WCb63BMOSez6ffPDdgw6-ncBBf1sL4WRoZm6d1MVwJRIZBb281d6Mu6d197HK2DMlvxyp_Wj-NnvL8ggEX19WFOc6P5V8RpeAlSTMn62h';
#prep the bundle
      
	 //$msg = array
       //   (
		//'body' 	=> 'Body  Of Notification',
		//'title'	=> 'Title Of Notification',
          //   	'icon'	=> 'myicon',/*Default Icon*/
            //  	'sound' => 'mySound'/*Default sound*/
          //);
		//echo $registrationIds;
         //exit;
//echo $registrationIds;		 
//echo '<pre>';print_r($registrationIds);
//exit;		 

    if ($deviceType==2) {
         $fields = array
			(
				'registration_ids'	=> $registrationIds,
				'notification'	=> $msg,
				'priority' => "high"
			); 

    } else {
        $fields = array
			(
				'registration_ids'	=> $registrationIds,
				'data'	=> $msg,
				'priority' => "high"
			);	
    }

	
	
	
	$headers = array
			(
				'Authorization: key=' . API_ACCESS_KEY,
				'Content-Type: application/json'
			);
#Send Reponse To FireBase Server	
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );

//echo '<pre>'; print_r(( $result ));		
#Echo Result Of FireBase Server
//echo $result;
//exit; 
?>