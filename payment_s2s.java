import com.google.gson.Gson; 
import com.sun.org.apache.xml.internal.security.utils.Base64; 
 
import javax.crypto.Mac; 
import javax.crypto.spec.SecretKeySpec; 
import java.io.BufferedReader; 
import java.io.DataOutputStream; 
import java.io.IOException; 
import java.io.InputStreamReader; 
import java.net.HttpURLConnection; 
import java.net.URL; 
import java.security.InvalidKeyException; 
import java.security.MessageDigest; 
import java.security.NoSuchAlgorithmException; 
import java.text.SimpleDateFormat; 
import java.util.Date; 
import java.util.HashMap; 
import java.util.TimeZone; 

class Main { 
 
    public static HashMap<String, String> getAuthorizationHeaders(String method,String path ,String body, String merchantKey, String merchantSalt) throws IOException, NoSuchAlgorithmException, InvalidKeyException { 
        try { 
            HashMap<String, String> headers = new HashMap<String, String>(); 
            Date today = new Date(); 
            SimpleDateFormat sdf = new SimpleDateFormat("EEE, dd MMM yyyy HH:mm:ss zzz"); 
            sdf.setTimeZone(TimeZone.getTimeZone("GMT")); 
            String date = sdf.format(today); 
            byte[] digest = MessageDigest.getInstance("SHA-256").digest(body.getBytes()); 
            String digestData = Base64.encode(digest); 
            String signingString = "date: " + date + "\n" + "digest: " + digestData; 
            SecretKeySpec signingKey = new SecretKeySpec(merchantSalt.getBytes(), "HmacSHA256"); 
            Mac mac = Mac.getInstance("HmacSHA256"); 
            mac.init(signingKey); 
            byte[] result = mac.doFinal(signingString.getBytes("ASCII")); 
            String hash = Base64.encode(result); 
            String authorization = "hmac username=" + "\"" + merchantKey + "\", algorithm=" + "\"hmac-sha256\", headers=" + "\"" + "date digest" + "\", signature=" + "\"" + hash + "\""; 
            headers.put("Date", date); 
            headers.put("Digest", digestData); 
            headers.put("Authorization", authorization); 
            System.out.println("header :"+headers); 
            return headers; 
        } catch(Exception e) { 
            e.printStackTrace(); 
        } 
        return null; 
    } 
 
 
    public static void postPaymentS2S() throws Exception { 
        String data ="{\"accountId\":\"smsplus\",\"referenceId\":\"99aefce2336f1c32ac23\",\"broker\":\"payu\",\"currency\":\"INR\",\"order\":{\"orderedItem\":[{\"itemId\":\"1\",\"description\":\"AAA\",\"quantity\":\"100\"},{\"itemId\":\"2\",\"description\":\"BBB\",\"quantity\":\"1000\"}],\"userDefinedFields\":{\"udf1\":\"\",\"udf2\":\"\",\"udf3\":\"\",\"udf4\":\"\",\"udf5\":\"\"},\"paymentChargeSpecification\":{\"price\":\"10.0\",\"taxSpecification\":{\"taxAmount\":\"10.0\"},\"convenienceFee\":\"20.0\",\"tdr\":\"10.0\",\"offersApplied\":[{\"offerId\":\"no_offer\",\"amount\":\"10.0\"}]}},\"paymentMethod\":{\"bankCode\":\"CC\",\"name\":\"CreditCard\",\"paymentCard\":{\"cardNumber\":\"5123456789012346\",\"issuer\":\"HDFC\",\"ownerName\":\"Test Payu\",\"validThrough\":\"05\\/2020\",\"cvv\":\"123\",\"brand\":\"VISA\",\"category\":\"CC\",\"countryCode\":\"IND\",\"nameOnCard\":\"Test Payu\",\"last 4 digits\":\"2346\"}},\"additionalInfo\":{\"storeCard\":\"false\",\"storeCardToken\":\"\",\"userCredentials\":\"\",\"enforcePaymethod\":\"\",\"dropCategory\":\"\",\"si\":\"\",\"forcePgid\":\"\",\"cardMerchantParam\":\"\",\"oneClickCheckout\":\"\",\"subventionAmount\":\"\",\"subventionEligibility\":\"\",\"txnS2sFlow\":\"1\",\"isAtmPin\":\"\",\"vpa\":\"\",\"vendorId\":\"\"},\"callBackActions\":{\"successAction\":\"https:\\/\\/pp32admin.payu.in\\/testresponsev2?action=successAction\",\"failureAction\":\"https:\\/\\/pp32admin.payu.intestresponsev2?action=failureAction\",\"cancelAction\":\"https:\\/\\/pp32admin.payu.in\\/testresponsev2?action=cancelAction\",\"codAction\":\"https:\\/\\/pp32admin.payu.in\\/testresponsev2?action=codAction\",\"bankAction\":\"sdf\"},\"billingDetails\":{\"address1\":\"Test Payu Gurgaon\",\"email\":\"testv2@example.in\",\"firstName\":\"Test User\",\"phone\":\"9123456789\"}}"; 
        String url = "https:/sandbox.payu.in/payments"; 
        URL obj = new URL(url); 
        HttpURLConnection con = (HttpURLConnection) obj.openConnection(); 
        HashMap headers=getAuthorizationHeaders("POST","/payments",data,"smsplus","ddwefw"); 
        con.setRequestMethod("POST"); 
        con.setRequestProperty("authorization", String.valueOf(headers.get("Authorization"))); 
        con.setRequestProperty("date", String.valueOf(headers.get("Date"))); 
        con.setRequestProperty("digest", String.valueOf(headers.get("Digest"))); 
 
        con.setDoOutput(true); 
        DataOutputStream wr = new DataOutputStream(con.getOutputStream()); 
        wr.writeBytes(data); 
        wr.flush(); 
        wr.close(); 
 
        int responseCode = con.getResponseCode(); 
        System.out.println("\nSending 'POST' request to URL : " + url); 
        System.out.println("Post parameters : " + data); 
        System.out.println("Response Code : " + responseCode); 
 
        BufferedReader in; 
        if(responseCode!=200){ 
            in = new BufferedReader(new InputStreamReader(con.getErrorStream())); 
        }else{ 
            in = new BufferedReader(new InputStreamReader(con.getInputStream())); 
        } 
        String inputLine; 
        StringBuffer response = new StringBuffer(); 
 
        while ((inputLine = in.readLine()) != null) { 
            response.append(inputLine); 
        } 
        in.close(); 
 
        Gson gson = new Gson(); 
        HashMap responseMap = gson.fromJson(response.toString(),HashMap.class); 
        //System.out.println(responseMap); 
        if(responseMap.get("status").toString().equals("success")){ 
            HashMap resultMap= (HashMap)responseMap.get("result"); 
            System.out.println("Form Post : "+new String (Base64.decode(resultMap.get("post_data").toString()))); 
        }else{ 
            System.out.println("Error Msg : "+responseMap.get("message").toString()); 
        } 
 
    } 

}
