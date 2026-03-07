<?php
// API access key from Google API's Console
$deviceToken = 'epB-4RpuWVw:APA91bE3Hh3QY1bniitgLXjewuohffVjqtYJ3mFolwUc24_o6yh2ElD5y_GeNqERDyTyft5v9Fqfu9kvjKIwTzcNKibHYteGAAYTA_g5jMHt0kD6M3gUEQiRnktLAq7xdgKfXwq33QqO';
define( 'API_ACCESS_KEY', 'AIzaSyC4zFKLHbCp7zk_yOiiXYRUJVM9j2MZi2Y' );
$registrationIds = array($deviceToken);
// prep the bundle
$msg = array
(
	'message' 	=> 'here is a message. message',
	'title'		=> 'This is a title. title',
	'subtitle'	=> 'This is a subtitle. subtitle',
	'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
	'vibrate'	=> 1,
	'sound'		=> 1,
	'largeIcon'	=> 'large_icon',
	'smallIcon'	=> 'small_icon'
);
$fields = array
(
	'registration_ids' 	=> $registrationIds,
	'data'			=> $msg
);
 
$headers = array
(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
);
 
$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );
echo $result;
?>