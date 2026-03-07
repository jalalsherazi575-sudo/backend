<?php
//$deviceToken = '1A4A4F1D6BC3D2785A7053DFF05E1AD179D4C18DD4EF7B637F23FCC15C07BD1E'; //$DeviceId;
$deviceToken = $DeviceId;
$passphrase = '1234';
//echo $deviceToken;
//exit;
//$message = 'My first push notification!';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
//$filename = 'apns-dev-cert.pem';
//live=CertificatesLive.pem
define("SITE_PATH234",dirname(dirname(__FILE__))."/",true);
$filename = SITE_PATH234.'ios-notification/Production_Grape_Push.pem';

//live $filename = SITE_PATH234.'ios-notification/samyda_child_production.pem';
//$filename = SITE_PATH234.'ios-notification/apns-dev-cert.pem';
stream_context_set_option($ctx, 'ssl', 'local_cert', $filename);
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
//ssl://gateway.sandbox.push.apple.com:2195
//ssl://gateway.push.apple.com:2195

$fp = stream_socket_client(
'ssl://gateway.push.apple.com:2195', $err,
$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

//if (!$fp)
//exit("Failed to connect: $err $errstr" . PHP_EOL);

//echo 'Connected to APNS' . PHP_EOL;



// Encode the payload as JSON
$payload = json_encode($body);
//echo $payload;
//exit; 
// Build the binary notification 
//echo '<pre>111'; print_r($deviceToken);
//exit;
foreach($deviceToken as $token){
//$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $token)) . pack        ("n",strlen($payload)) . $payload;
$result = fwrite($fp, $msg, strlen($msg));
}

// Send it to the server
//$result = fwrite($fp, $msg, strlen($msg));

/* if (!$result)
    echo 'Message not delivered' . PHP_EOL;
else
    echo 'Message successfully delivered'.PHP_EOL; */

// Close the connection to the server
fclose($fp);

?>