<?php
$deviceToken = '463A4FD7AB40BF463FCEC31BF1DC9AC8A560FBA5DAEFF2A47FEAD6070F19DB68'; //$DeviceId;
$deviceToken = $DeviceId;
$passphrase = '1234';

$message = 'My first push notification!';

////////////////////////////////////////////////////////////////////////////////
//echo "dddd";
//exit;
$ctx = stream_context_create();
//$filename = 'apns-dev-cert.pem';
define("SITE_PATH234",dirname(dirname(__FILE__))."/",true);
$filename = SITE_PATH234.'ios-notification/ParentPushChatKey.pem';
//$filename = SITE_PATH234.'ios-notification/apns-dev-cert.pem';
stream_context_set_option($ctx, 'ssl', 'local_cert', $filename);
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
//ssl://gateway.sandbox.push.apple.com:2195
//ssl://gateway.push.apple.com:2195

$fp = stream_socket_client(
'ssl://gateway.sandbox.push.apple.com:2195', $err,
$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

//if (!$fp)
//exit("Failed to connect: $err $errstr" . PHP_EOL);

//echo 'Connected to APNS' . PHP_EOL;



// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . 

$payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

 if (!$result)
    echo 'Message not delivered' . PHP_EOL;
else
    echo 'Message successfully delivered'.PHP_EOL; 

// Close the connection to the server
fclose($fp);

?>