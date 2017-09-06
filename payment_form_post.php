public static function getHmacParameters($method, $path, $content, $username, $salt) { 
 
    $version = "HTTP/1.1" ; 
 
    $requestLine = strtoupper($method) . " " . $path . " " . $version; 
    $dateString = gmdate('D, d M Y H:i:s T'); 
    $digest = base64_encode(hash("sha256", $content, true)); 
 
    $signing_headers = [ 
        'date' => $dateString, 
        'request-line' => $requestLine, 
        'digest' => $digest 
    ]; 
 
    $signing_string = ""; 
    $headers = ""; 
    foreach ($signing_headers as $key => $value) { 
        if ($key != "request-line") { 
            $signing_string .= $key . ":" . " "; 
        } 
        $signing_string .= $value . "\n"; 
        $headers .= $key . " "; 
    } 
    $signing_string = rtrim($signing_string, "\n"); 
    $headers = rtrim($headers, " "); 
 
    $hmacHash = hash_hmac("sha1", $signing_string, $salt, true); 
 
    $signature = base64_encode($hmacHash); 
    $authorization = "hmac username=\"$username\", algorithm=\"hmac-sha1\", headers=\"$headers\", signature=\"$signature\""; 
 
    return [ 
        'dateString' => $dateString, 
        'digest' => $digest, 
        'authorization' => $authorization 
    ]; 
 
} 


public static function postPaymentFormData() { 
$merchantKey='smsplus'; 
$merchantSalt='ddwefw'; 
$randomTxnId = substr(hash('sha256', mt_rand() . microtime()), 0, 20); 

//dummy object to test with
$data = '{ 
  "accountId": "' . $merchantKey . '", 
  "referenceId": "' . $randomTxnId . '", 
  "broker": "payu", 
  "currency": "INR", 
  "order": { 
    "orderedItem": [ 
      { 
        "itemId": "1", 
        "description": "AAA", 
        "quantity": "100" 
      }, 
      { 
        "itemId": "2", 
        "description": "BBB", 
        "quantity": "1000" 
      } 
    ], 
    "userDefinedFields": { 
      "udf1": "", 
      "udf2": "", 
      "udf3": "", 
      "udf4": "", 
      "udf5": "" 
    }, 
    "paymentChargeSpecification": { 
      "price": "10.0", 
      "taxSpecification": { 
        "taxAmount": "10.0" 
      }, 
      "convenienceFee": "20.0", 
      "tdr": "10.0", 
      "offersApplied": [ 
        { 
          "offerId": "no_offer", 
          "amount": "10.0" 
        } 
      ] 
    } 
  }, 
  "paymentMethod": { 
    "bankCode": "CC", 
    "name": "CreditCard", 
    "paymentCard": { 
      "cardNumber": "5123456789012346", 
      "issuer": "HDFC", 
      "ownerName": "Test Payu", 
      "validThrough": "05/2020", 
      "cvv": "123", 
      "brand": "VISA", 
      "category": "CC", 
      "countryCode": "IND", 
      "nameOnCard": "Test Payu", 
      "last 4 digits": "2346" 
    } 
  }, 
  "additionalInfo": { 
    "storeCard": "false", 
    "storeCardToken": "", 
    "userCredentials": "", 
    "enforcePaymethod": "", 
    "dropCategory": "", 
    "si": "", 
    "forcePgid": "", 
    "cardMerchantParam": "", 
    "oneClickCheckout": "", 
    "subventionAmount": "", 
    "subventionEligibility": "", 
    "txnS2sFlow": "", 
    "isAtmPin": "", 
    "vpa": "", 
    "vendorId": "" 
  }, 
  "callBackActions": { 
    "successAction": "https://pp32admin.payu.in/testresponsev2?action=successAction", 
    "failureAction": "https://pp32admin.payu.in/testresponsev2?action=failureAction", 
    "cancelAction": "https://pp32admin.payu.in/testresponsev2?action=cancelAction", 
    "codAction": "https://pp32admin.payu.in/testresponsev2?action=codAction", 
    "bankAction": "" 
  }, 
  "billingDetails": { 
    "address1": "Test Payu Gurgaon", 
    "email": "testv2@example.in", 
    "firstName": "Test User", 
    "phone": "9123456789" 
  } 
}'; 
 
$data=htmlspecialchars_decode($data, ENT_QUOTES); 
$content=str_replace("br /", "", $data); 
$hmacParams = getHmacParameters('POST', '/payments', $content, $merchantKey, $merchantSalt); 
$content=urlencode($content);  
$digest = urlencode($hmacParams['digest']); 
$dateString = urlencode($hmacParams['dateString']); 
$authorization = urlencode($hmacParams['authorization']); 
$post_string='data='.$content.'&digest='.$digest.'&date='.$dateString.'&authorization='.$authorization; 
$curl_connection = curl_init('https://sandbox.payu.in/payments'); 
curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30); 
curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"); //curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true); curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1); 
curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string); 
$result = curl_exec($curl_connection); 
curl_close($curl_connection); 
} 