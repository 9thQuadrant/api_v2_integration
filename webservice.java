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
 

    public static void sendWebService(String data,String path,String method) throws Exception { 
        String url = "https://sandbox.payu.in"+path; 
        URL obj = new URL(url); 
        HttpURLConnection con = (HttpURLConnection) obj.openConnection(); 
        HashMap headers=getAuthorizationHeaders(method,path,data,"smsplus","ddwefw"); 
        con.setRequestMethod(method); 
        con.setRequestProperty("Content-Type", "application/json"); 
        con.setRequestProperty("authorization", String.valueOf(headers.get("Authorization"))); 
        con.setRequestProperty("date", String.valueOf(headers.get("Date"))); 
        con.setRequestProperty("digest", String.valueOf(headers.get("Digest"))); 
        //con.setRequestProperty("host", "api.payu.in"); 
 
        if(method.equalsIgnoreCase("POST") && !data.isEmpty() && data!=null){ 
            con.setDoOutput(true); 
            byte[] outputBytes = data.getBytes("UTF-8");  
            OutputStream os = con.getOutputStream();  
            os.write(outputBytes);  
            os.flush(); 
            os.close(); 
        } 
 
        int responseCode = con.getResponseCode(); 
        System.out.println("\nSending request to URL : " + url); 
        System.out.println("Parameters : " + data); 
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
 
        System.out.println(response.toString()); 
 
    }

}
