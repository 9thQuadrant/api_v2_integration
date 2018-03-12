public static function getHmacParameters($method, $path, $content, $username, $salt) { 
 
    $dateString = gmdate('D, d M Y H:i:s T'); 
    $digest = base64_encode(hash("sha256", $content, true)); 
 
    $signing_headers = [ 
        'date' => $dateString, 
        'digest' => $digest 
    ]; 
 
    $signing_string = ""; 
    $headers = ""; 
    foreach ($signing_headers as $key => $value) { 
        $signing_string .= $key . ":" . " "; 
        $signing_string .= $value . "\n"; 
        $headers .= $key . " "; 
    } 
    $signing_string = rtrim($signing_string, "\n"); 
    $headers = rtrim($headers, " "); 
 
    $hmacHash = hash_hmac("sha256", $signing_string, $salt, true); 
 
    $signature = base64_encode($hmacHash); 
    $authorization = "hmac username=\"$username\", algorithm=\"hmac-sha256\", headers=\"$headers\", signature=\"$signature\""; 
 
    return [ 
        'dateString' => $dateString, 
        'digest' => $digest, 
        'authorization' => $authorization 
    ]; 
 
} 


public static function sendWebService($merchantKey,$path, $reqBody,$method, $merchantSalt) { 
         
if($reqBody==null ){ 
    $reqBody = ''; 
} 
 
$url = 'https://sandbox.payu.in' . $path; 
 
getHmacParameters($method, $path, $reqBody, $merchantKey, $merchantSalt); 
 
$ch = curl_init() or die(curl_error($ch)); 
curl_setopt($ch, CURLOPT_URL, $url); 
 
$headers = array( 
    "date: " . $hmacParams['dateString'], 
    "digest: " . $hmacParams['digest'], 
    "authorization: " . $hmacParams['authorization'] 
); 
 
if ($method == 'POST') { 
    curl_setopt($ch, CURLOPT_POST, 1); 
} else if ($method != 'GET'){ 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
} 
 
if ($reqBody!='' && $method != 'GET') { 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $reqBody); 
    $headers[] = 'Content-Type: application/json'; 
} 
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
 
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch, CURLOPT_HEADER, 1); 
//        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
 
$response = curl_exec($ch); 
list($responseHeader, $content) = explode("\r\n\r\n", $response, 2); 
$responseHeaders = explode("\n", $responseHeader); 
 
$info = curl_getinfo($ch); 
 
$statusLabel = "Http status code: " . $info["http_code"]; 
 
$curlErrorNo = curl_errno($ch); 
if ($curlErrorNo) { 
    $message = curl_error($ch); 
    $statusLabel = "Curl call failed!. Error-code: " . $curlErrorNo . " Error-msg: " . $message; 
} 
 
echo $content; 
         
} 


