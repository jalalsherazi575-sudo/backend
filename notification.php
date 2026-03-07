<?php
// Get Request X-JWS-Signature header
$jws = isset($_SERVER['HTTP_X_JWS_SIGNATURE']) ? $_SERVER['HTTP_X_JWS_SIGNATURE'] : null;
if (null === $jws) {
    exit('FALSE - Missing JSW header');
}
// Extract JWS header properties
$jwsData = explode('.', $jws);
$headers = isset($jwsData[0]) ? $jwsData[0] : null;
$signature = isset($jwsData[2]) ? $jwsData[2] : null;
if (null === $headers || null === $signature) {
    exit('FALSE - Invalid JWS header');
}
// Decode received headers json string from base64_url_safe
$headersJson = base64_decode(strtr($headers, '-_', '+/'));
// Get x5u header from headers json
$headersData = json_decode($headersJson, true);
$x5u = isset($headersData['x5u']) ? $headersData['x5u'] : null;
if (null === $x5u) {
    exit('FALSE - Missing x5u header');
}
// Check certificate url
$prefix = 'https://secure.tpay.com';
if (substr($x5u, 0, strlen($prefix)) !== $prefix) {
    exit('FALSE - Wrong x5u url');
}
// Get JWS sign certificate from x5u uri
$certificate = file_get_contents($x5u);
// Verify JWS sign certificate with Tpay CA certificate
// Get Tpay CA certificate to verify JWS sign certificate. CA certificate be cached locally.
$trusted = file_get_contents('https://secure.tpay.com/x509/tpay-jws-root.pem');
// in php7.4+ with ext-openssl you can use openssl_x509_verify
if (1 !== openssl_x509_verify($certificate, $trusted)) {
    exit('FALSE - Signing certificate is not signed by Tpay CA certificate');
}
// or using phpseclib
$x509 = new \phpseclib3\File\X509();
$x509->loadX509($certificate);
$x509->loadCA($trusted);
if (!$x509->validateSignature()) {
    exit('FALSE - Signing certificate is not signed by Tpay CA certificate');
}
// Get request body
$body = file_get_contents('php://input');
// Encode body to base46_url_safe
$payload = str_replace('=', '', strtr(base64_encode($body), '+/', '-_'));
// Decode received signature from base64_url_safe
$decodedSignature = base64_decode(strtr($signature, '-_', '+/'));
// Verify RFC 7515: JSON Web Signature (JWS) with ext-openssl
// Get public key from certificate
$publicKey = openssl_pkey_get_public($certificate);
if (1 !== openssl_verify($headers . '.' . $payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256)) {
    exit('FALSE - Invalid JWS signature');
}
// or using phpseclib
$publicKey = $x509->getPublicKey()
    ->withHash('sha256')
    ->withPadding(\phpseclib3\Crypt\RSA::SIGNATURE_PKCS1);
if (!$publicKey->verify($headers . '.' . $payload, $decodedSignature)) {
    exit('FALSE - Invalid JWS signature');
}
// JWS signature verified successfully.
// Process request data and send valid response to notification service.
$transactionData = $_POST;
echo 'TRUE';