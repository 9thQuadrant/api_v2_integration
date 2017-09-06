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


public static function postPaymentS2S() { 
      $merchantKey='smsplus'; 
      $merchantSalt='ddwefw'; 
      $randomTxnId = substr(hash('sha256', mt_rand() . microtime()), 0, 20); 

      //dummy data to test with
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
          "txnS2sFlow": "1", 
          "isAtmPin": "", 
          "vpa": "", 
          "vendorId": "" 
        }, 
        "callBackActions": { 
          "successAction": "https://pp32admin.payu.in/testresponsev2?action=successAction", 
          "failureAction": "https://pp32admin.payu.in/testresponsev2?action=failureAction", 
          "cancelAction": "https://pp32admin.payu.in/testresponsev2?action=cancelAction", 
          "codAction": "https://pp32admin.payu.in/testresponsev2?action=codAction", 
          "bankAction": "sdf" 
        }, 
        "billingDetails": { 
          "address1": "Test Payu Gurgaon", 
          "email": "testv2@example.in", 
          "firstName": "Test User", 
          "phone": "9123456789" 
        } 
      }'; 
      //$data=htmlspecialchars_decode($data, ENT_QUOTES); 
      $content=str_replace("br /", "", $data); 
      $parsedJson = json_decode($content, true); 
      $content= json_encode($parsedJson); 
       
      $hmacParams = getHmacParameters('POST', '/payments', $content, $merchantKey, $merchantSalt); 
      $digest=$hmacParams['digest']; 
      $authorization=$hmacParams['authorization']; 
      $date=$hmacParams['dateString']; 
       
      $headers=array( 
          "authorization:$authorization", 
          "date:$date", 
          "digest:$digest" 
      ); 
       
      $url='https://sandbox.payu.in/payments'; 
      $ch = curl_init() or die( curl_error() ); 
      $port = (strpos( $url, 'https' ) === FALSE) ? 80 : 443; 
      curl_setopt( $ch, CURLOPT_PORT, $port ); // port 443 
      curl_setopt( $ch, CURLOPT_POST, 1 ); 
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $content ); // posting the request string 
      curl_setopt( $ch, CURLOPT_URL, $url ); 
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 ); 
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); 
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 ); 
      curl_setopt($ch, CURLOPT_HEADER, true); 
      curl_setopt($ch, CURLOPT_HTTPHEADER,$headers); 
      $result = curl_exec( $ch ); 
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); 
      $body = substr($result, $header_size); 
      $body=json_decode($body,true); 
      if($body['status']=="success"){ 
        echo base64_decode($body['result']['post_data']); 
      }else{ 
        echo $body['message']; 
      } 
}