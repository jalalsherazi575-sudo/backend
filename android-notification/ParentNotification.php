<?php
#API access key from Google API's Console
    define( 'API_ACCESS_KEY', 'AAAAsDXvsBM:APA91bFWj9-zG-QXvf4eekfSbob7H_eQhoYipU052TraOCqggHxDSnEa0MOguoEUx3N_p69ooO1blIEMapVhoovciqkgIoHWVYIEkhBiLNParkQVOy18lHAHbLqie7S0V5_p3UebMRqj' );
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
//echo '<pre>';print_r($msg);
//exit;		 
	$fields = array
			(
				'registration_ids'		=> $registrationIds,
				'data'	=> $msg
			);
	
	 
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
#Echo Result Of FireBase Server
//echo $result;
//exit;
?>