<?php

require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'facebook' . DS  . 'vendor'. DS . 'autoload.php');
require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'google' . DS  . 'vendor'. DS . 'autoload.php');
require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'phpmailer' . DS  . 'vendor'. DS . 'autoload.php');
require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'googlenew' . DS  . 'autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\Likelihood;
use Google\Cloud\Storage\StorageClient;


class Utility
{



    static function isJsonError($data)
    {
        json_decode($data);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return "false";

            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';

            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';

            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';

            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';

            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';

            default:
                return ' - Unknown error';

        }
    }
    public static function getLocationFromIP($ip){



        //https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=31.45622259,73.12973031&destinations=31.40985980,73.11785060&key=

        $url  = "http://www.geoplugin.net/json.gp?ip=" .  $ip;



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);




        $output_array = json_decode($response,'true');





        return $output_array;


    }


    public static function compressImage($image_url,$folder_url){




        $new = file_get_contents($image_url);

        $fileName  = uniqid();
        list($width_orig, $height_orig) = getimagesizefromstring($new);


        $width = 120;


        $aspectRatio = $height_orig / $width_orig;
        $height = intval($aspectRatio * $width);
        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromstring(file_get_contents($image_url));
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig,$height_orig);


        imagejpeg( $image_p, $folder_url, 100 );
        return $folder_url;


    }
    function uploadOriginalVideoFileIntoTemporaryFolder($param)
    {

        $fileName = uniqid();
        $folder = TEMP_UPLOADS_FOLDER_URI;
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        if($param == "image"){

            $ext = ".png";

        }else
            if($param == "video"){

                $ext = ".mp4";

            }else
                if($param == "audio"){

                    $ext = ".mp3";

                }else  if($param == "json"){

                    $ext = ".json";

                }
        $filePath = $folder . "/" . $fileName . $ext;

        if (move_uploaded_file($_FILES[$param]['tmp_name'], $filePath)) {


            return $filePath;

        }else{

            return false;
        }
    }

    public function ss(){


        $client = new Google_Client();
        $jsfile = ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'googlenew/client_secret_1027624760309-045eticu9msgucau4qqi2b6tjkrlf4e4.apps.googleusercontent.com.json';

        $client->setAuthConfig($jsfile);

        $service = new Google_Service_Books($client);
        $optParams = array(
            'filter' => 'free-ebooks',
            'q' => 'Henry David Thoreau'
        );
        $results = $service->volumes->listVolumes($optParams);

        foreach ($results->getItems() as $item) {
            echo $item['volumeInfo']['title'], "<br /> \n";
        }
    }

    public static function checkAdult($uri){





        $options = [];
        $video = new VideoIntelligenceServiceClient([
            'credentials' => ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'googlenew/codecanyon-31e70d5c8d78.json'
        ]);

# Execute a request.
        $operation = $video->annotateVideo([
            'inputUri' => "gs://cloud-samples-data/video/cat.mp4",
            'features' => [Feature::EXPLICIT_CONTENT_DETECTION]
        ]);

# Wait for the request to complete.
        $operation->pollUntilComplete($options);

# Print the result.
        if ($operation->operationSucceeded()) {
            $results = $operation->getResult()->getAnnotationResults()[0];
            $explicitAnnotation = $results->getExplicitAnnotation();
            foreach ($explicitAnnotation->getFrames() as $frame) {
                $time = $frame->getTimeOffset();
                printf('At %ss:' . PHP_EOL, $time->getSeconds() + $time->getNanos()/1000000000.0);
                printf('  pornography: ' . Likelihood::name($frame->getPornographyLikelihood()) . PHP_EOL);
            }
        } else {
            print_r($operation->getError());
        }
       

    }
    public static function getGoogleUserInfo($access_token){


        if(strlen($access_token) > 500) {
            $CLIENT_ID = GOOGLE_CLIENT_ID;
            $client = new Google_Client(['client_id' => $CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($access_token);
            if ($payload) {

                return true;

            } else {
                return false;
            }
        }else{

            return false;
        }
    }


    public static function getFacebookUserInfo($access_token){

        //$access_token = "EAAHHnWt5954BAKysBA1giqTqE5f6XPLWoY2ztYdsQ8lc4ODXdS8zi36L2ZBiSXunPsfJoXsTBLMjpp7kTcwHHSIdgzNfT1JOxIRQ6cugQoPNFZBjrfqNEyOm1LZA3CYDYOMUoG49P0oyjpIhcfZCVSC8oKR0U6P17TaqgnzxYH7Bm8k0NID8oC643PmICWlzXV1NLVMFzQZDZD";


        $facebook = new \Facebook\Facebook([
            'app_id'      => FACEBOOK_APP_ID,
            'app_secret'     => FACEBOOK_APP_SECRET,
            'default_graph_version'  => FACEBOOK_GRAPH_VERSION
        ]);

        $access_token = $access_token;
       // $graph_response = $facebook->get("/me?fields=name,email", $access_token);

        try {
            // Returns a `FacebookFacebookResponse` object
            $response = $facebook->get(
                '/me',
               $access_token
            );
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            return false;
        }catch(Facebook\Exceptions\FacebookSDKException $e) {
            return false;

        }

        return true;
      //  $graphNode = $response->getGraphNode();
      //  $facebook_user_info = $graph_response->getGraphUser();
        /*if(!empty($facebook_user_info['id']))
        {
           return true;
        }else{

            return false;
        }*/
    }
    static function unlinkFile($file_path){
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        return true;
    }
    static function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

    public static function apache_request_headers(){


        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);

                    $arh_key = implode('-', $rx_matches);

                }

                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }
   



    static function get_hashtags($body) {
        $hashtag_set = [];
        $array = explode('#', $body);

        foreach ($array as $key => $row) {
            $hashtag = [];
            if (!empty($row)) {
                $hashtag =  explode(' ', $row);
                $hashtag_set[] = $hashtag[0];
            }
        }
        return $hashtag_set;
    }

    public static function generateSessionToken(){


        $token = base64_encode(random_bytes(64));
        $token = strtr($token, '+/', '-_');
        return $token;

    }

    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }


    static function resize($newWidth, $targetFile, $originalFile,$user_id,$folder_url) {
       

        $info = getimagesize($originalFile);
        $mime = $info['mime'];


        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func = 'imagejpeg';
                $new_image_ext = '.jpg';
                break;

            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func = 'imagepng';
                $new_image_ext = '.png';
                break;

            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func = 'imagegif';
                $new_image_ext = '.gif';
                break;

            default:
                throw new Exception('Unknown image type.');
        }

        $img = @$image_create_func($originalFile);
        list($width, $height) = getimagesize($originalFile);

        $newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

       /* if (file_exists($targetFile)) {
            unlink($targetFile);

        }*/
        $arr = explode('/', $targetFile);
        $array_total = count($arr);
        $image_name = $arr[$array_total - 1];
        $new_image_name = $newWidth."_".$image_name;
        $folder = $folder_url . '/' . $user_id.'/';
        $targetFile = $folder.$new_image_name;
         $image_save_func($tmp, $targetFile);
         return $targetFile;
    }

    public static function getDurationTimeBetweenTwoDistances($lat1,$long1,$lat2,$long2){



        //https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=31.45622259,73.12973031&destinations=31.40985980,73.11785060&key=

        $url  = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&key=".GOOGLE_MAPS_KEY;



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);




        $output_array = json_decode($response,'true');




        if(!is_array($output_array)){
            return false;
        }
        if (array_key_exists('error_message', $output_array)){
            return false;

        }


        if($output_array['rows'][0]['elements'][0]['status'] =="ZERO_RESULTS"
            || $output_array['rows'][0]['elements'][0]['status'] =="NOT_FOUND" ){
            return false;

        }


        else{

            return $output_array;
        }

    }



    static function calculateFare($base_fare,$cost_per_minute,$cost_per_distance,$ride_duration_in_seconds,$ride_distance_in_meters,$surge,$distance_unit){
        $ride_duration_in_minute = $ride_duration_in_seconds/60;
        $ride_distance_in_miles = $ride_distance_in_meters * 0.00062137;
        $ride_distance_in_km = $ride_distance_in_meters/100;

        if($distance_unit == "M"){

           $fare =  $base_fare + ($cost_per_minute * $ride_duration_in_minute) + ($cost_per_distance * $ride_distance_in_miles);

        }else  if($distance_unit == "K"){

            $fare =  $base_fare + ($cost_per_minute * $ride_duration_in_minute) + ($cost_per_distance * $ride_distance_in_km);

        }

        $estimated['fare'] = round($fare, 1);
        $estimated['time'] = round($ride_duration_in_minute);

        return $estimated;



}
    static function uploadFileintoFolder($user_id, $data, $folder_url)
    {


        //$ext = pathinfo('/testdir/dir2/image.gif', PATHINFO_EXTENSION);
        $fileName = uniqid();

        $file = base64_decode($data['file_data']);


        $folder = $folder_url . '/images';


        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $filePath = $folder . "/" . $fileName . '.png';
        file_put_contents($filePath, $file);
        return $filePath;

    }

    static function uploadFileintoFolderDir($data, $folder_url,$extension = null)
    {


        //$ext = pathinfo('/testdir/dir2/image.gif', PATHINFO_EXTENSION);
        $fileName = uniqid();

        $file = base64_decode($data);


        $folder = $folder_url . '/';


        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        if(is_null($extension)){

            $filePath = $folder . "/" . $fileName . '.png';
        }else{

            $filePath = $folder . "/" . $fileName . '.'.$extension;

        }
        file_put_contents($filePath, $file);
        return $filePath;

    }
    static function getDurationOfVideoFile($video_url){

        $cmd = "ffprobe -i $video_url -show_format  -v quiet | sed -n 's/duration=//p'";
        exec($cmd,$output);
        $duration = number_format((float)$output[0], 1, '.', '');
        return $duration;
    }

    static function getDurationofAudioFile($filepath){

        $duration = shell_exec("ffmpeg -i \"". $filepath . "\" 2>&1");


        preg_match("/Duration: (\d{2}:\d{2}:\d{2}\.\d{2})/",$duration,$matches);

        $time = explode(':',$matches[1]);
        $hour = $time[0];
        $minutes = $time[1];
        $seconds = round($time[2]);

        $total_seconds = 0;
        $total_seconds += 60 * 60 * $hour;
        $total_seconds += 60 * $minutes;

        return $minutes.":".$seconds;

    }
    static function getToken($length)
    {
        $token        = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[Utility::crypto_rand_secure(0, strlen($codeAlphabet))];
        }
        return $token;
    }


    public static function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 0)
            return $min; // not so random...
        $log    = log($range, 2);
        $bytes  = (int) ($log / 8) + 1; // length in bytes
        $bits   = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }


    public static function randomNumber($length) {
        $result = '';

        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }


    function UnderscoreExist($string){

        if (preg_match('/^[a-z]+_[a-z]+$/i', $string)) {
            return true;
            // contains an underscore and is two words
        } else {
            // does not contain two words, or an underscore
            return false;
        }
    }


    function getValueBeforeUnderscore($string){


        $final_string = strstr($string, '_', true);
        if(strlen($final_string) > 0){

            return $final_string;
        }else{

            return false;

        }

    }

    function getValueAfterUnderscore($string){

        if($this->UnderscoreExist($string)) {
            $final_string = substr($string, strpos($string, "_") + 1);
            if (strlen($final_string) > 0) {

                return $final_string;
            } else {

                return false;

            }
        }else{

            return false;
        }
    }



    public static function sendPushNotificationToMobileDevice($data){



        $key=FIREBASE_PUSH_NOTIFICATION_KEY;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: key=".$key."",
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 85f96364-bf24-d01e-3805-bccf838ef837"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    public static function sendSmsVerificationCurl($to_number,$msg)
    {


        $id = TWILIO_ACCOUNTSID;
        $token = TWILIO_AUTHTOKEN;
        $url = "https://api.twilio.com/2010-04-01/Accounts/$id/SMS/Messages.json";
        $from = TWILIO_NUMBER;
        $to = $to_number; // twilio trial verified number
        $body = $msg;
        $data = array (
            'From' => $from,
            'To' => $to,
            'Body' => $body,
        );
        $post = http_build_query($data);
        $x = curl_init($url );
        curl_setopt($x, CURLOPT_POST, true);
        curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
        curl_setopt($x, CURLOPT_POSTFIELDS, $post);
        $y = curl_exec($x);

        curl_close($x);
        return json_decode($y,true);




    }

    public static function  getAge($dob){



        $today = new DateTime();

        $birthdate = new DateTime($dob);
        $interval = $today->diff($birthdate);
        $age = $interval->format('%y');
        return $age;
    }

    public static function sendSmsVerification($to_number,$msg)
    {


        $phone_verify = ClassRegistry::init('Setting')->getActiveAgainstCategory("phone_verify",1);



        if(count($phone_verify) > 2 ) {

            $company_name = (new self)->getValueBeforeUnderscore($phone_verify[0]['Setting']['type']);


            $unknown_type1 = (new self)->getValueAfterUnderscore($phone_verify[0]['Setting']['type']);
            $unknown_type2 = (new self)->getValueAfterUnderscore($phone_verify[1]['Setting']['type']);
            $unknown_type3 = (new self)->getValueAfterUnderscore($phone_verify[2]['Setting']['type']);

            $unknown_source1 = $phone_verify[0]['Setting']['source'];
            $unknown_source2 = $phone_verify[1]['Setting']['source'];
            $unknown_source3 = $phone_verify[2]['Setting']['source'];

            if ($unknown_type1 && $unknown_type2 && $unknown_type3) {
                if ($unknown_type1 == "key") {

                    $key = $unknown_source1;
                } else if ($unknown_type2 == "key") {

                    $key = $unknown_source2;

                } else if ($unknown_type3 == "key") {

                    $key = $unknown_source3;
                }


                if ($unknown_type1== "secret") {

                    $secret = $unknown_source1;
                } else if ($unknown_type2 == "secret") {

                    $secret = $unknown_source2;

                } else if ($unknown_type3 == "secret") {

                    $secret = $unknown_source3;
                }


                if ($unknown_type1 == "number") {

                    $from_number = $unknown_source1;

                } else if ($unknown_type2 == "number") {

                    $from_number = $unknown_source2;

                } else if ($unknown_type3 == "number") {

                    $from_number = $unknown_source3;

                }


                if ($company_name == NEXMO || $company_name == TWILIO) {


                    switch ($company_name) {
                        case NEXMO:
                            $url = NEXMO_URL;

                            $data = array(
                                'api_key' => $key,
                                'api_secret' => $secret,
                                'to' => $to_number,
                                'from' => $from_number,
                                'text' => $msg,
                            );

                            break;

                        case TWILIO:



                            $url = TWILIO_URL . $key . "/SMS/Messages.json";

                            $data = array(
                                'From' => $from_number,
                                'To' => $to_number,
                                'Body' => $msg,
                                'accountid'=>$key,
                                'token'=>$secret
                            );

                            break;

                        default:
                            echo "";
                    }



                    $post = http_build_query($data);
                    $x = curl_init($url);
                    curl_setopt($x, CURLOPT_POST, true);
                    curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

                    if ($company_name == TWILIO) {
                        curl_setopt($x, CURLOPT_USERPWD, "$key:$secret");
                    }
                    curl_setopt($x, CURLOPT_POSTFIELDS, $post);
                    $y = curl_exec($x);

                    curl_close($x);
                    $final_result = json_decode($y, true);


                    if($company_name == NEXMO){

                        if (array_key_exists("error-text",$final_result['messages'][0])){

                            //$final_result['messages'][0]['data'] = $data;
                            $output['code'] = 203;
                            $output['msg'] = $final_result['messages'][0]['error-text'];
                            $output['msg_from_company'] = $final_result;
                            $output['data'] = $data;
                            return $output;




                        }else  if (array_key_exists("status",$final_result['messages'][0])){

                            if($final_result['messages'][0]['status'] == 0){

                                $output['code'] = 200;
                                $output['msg'] = $final_result['messages'][0]['error-text'];
                                $output['msg_from_company'] = $final_result;
                                $output['data'] = $data;
                                return $output;

                            }else{

                                $output['code'] = 203;
                                $output['msg'] = $final_result['messages'][0]['status'];
                                $output['msg_from_company'] = $final_result;
                                $output['data'] = $data;
                                return $output;


                            }

                        }else{


                            $output['code'] = 203;
                            $output['msg'] = UNKNOWN_ERROR;
                            $output['msg_from_company'] = $final_result;
                            $output['data'] = $data;
                            return $output;

                        }



                    }else if($company_name == TWILIO){



                        if (array_key_exists('code', $final_result)){
                            if($final_result['code'] == 21608 || $final_result['code'] == 201 || $final_result['code'] ==21606  || $final_result['code'] ==20003){

                                $output['code'] = 203;
                                $output['msg']  = $final_result['message'];
                                $output['msg_from_company'] = $final_result;
                                $output['data'] = $data;
                                return $output;

                            }
                        }


                    }


                }else{

                    $output['code'] = 201;
                    $output['msg'] = "Company is invalid";
                    return $output;
                }
            }else{

                $output['code'] = 201;
                $output['msg'] = "underscore is missing in the database value";
                return $output;

            }
        }else{

            $output['code'] = 201;
            $output['msg'] = "Something is missing in the database. There should be three values in the database(key,secret,number)";
            return $output;

        }
    }


    static function getCloudFrontUrl($url,$before_string){


        $aws=strpos($url,'amazonaws');

        if($aws)
        {

            $s3_url = substr($url, 0, strpos($url, $before_string));
            return str_replace($s3_url,CLOUDFRONT_URL,$url);




        }else{

            return $url;
        }
    }

    static function sendMail($data){


        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = MAIL_HOST;                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = MAIL_USERNAME;                     // SMTP username
            $mail->Password   = MAIL_PASSWORD;                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom(MAIL_FROM, MAIL_NAME);
            // $mail->addAddress('irfanzsheikhz@gmail.com', 'Irfan Sheikh');     // Add a recipient
            $mail->addAddress($data['to'],$data['name']);               // Name is optional
            $mail->addReplyTo(MAIL_REPLYTO);
            // $mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            // Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $data['subject'];
            $mail->Body    = $data['message'];
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            $array['code'] = 200;
            $array['msg'] = "success";

            return $array;
        } catch (Exception $e) {

            $array['code'] = 201;
            $array['msg'] =  $mail->ErrorInfo;

            return $array;
        }

    }



}

?>