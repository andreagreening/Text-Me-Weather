<?php 
namespace App\Libraries;

use Illuminate\Http\Request;
use Auth;
use User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;
use Twilio\Rest\Client as TwilioClient;
use Log;
use Cache;

class Helpers
{
	public static function findZip($smsBody, $replyTo)
	{
		preg_match("/\b\d{5}\b/", $smsBody, $matches);
        $zip = $matches;
        if(! $matches){
        	$keyword = Helpers::findKeyword($smsBody);
        	if($keyword == 'help'){
        		Helpers::helpMessage($replyTo);  
        	}
        	$replyBody = "You did not send a valid zip code. Text 'help' to access the help menu.";
        	Helpers::sendMessage($replyTo, $replyBody); 
        	Log::debug($replyTo . ' did not send a valid zip code. Drizzle sent the following message ' . $replyBody);
        	die();
        }
        return $zip;
	}

	public static function getLocation($zip, $replyTo){
		$googleApiKey = env('GOOGLE_MAPS_API_KEY');
        if(Cache::has($zip)){
			return Cache::get($zip);
		}
		$client = new GuzzleClient();
        try {
        	$res = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $zip . '&key=' . $googleApiKey);	
        } catch (GuzzleException $e) {
            // Log::debug('Unable to recieve valid location data.' . $replyTo);
            Log::debug((array) $e->getMessage());
            return false;
        	// I need to send an error message to the user when this is firing! 
        }
        $response = (string) $res->getBody();
        $geocodeArray = json_decode($response, true);
        // make sure the correct zip is returned, because google is trying to match
    	//	anything from the address
    	if(! isset($geocodeArray['results'][0]['address_components'])) return false;
    	foreach($geocodeArray['results'][0]['address_components'] as $address_component){
    		//	since these array keys are not named, we have to loop
    		if(in_array('postal_code', $address_component['types'])){
    			//	we are within the zip code section of the results
    			if($address_component['long_name'] != $zip){
    				Helpers::errorMessage($replyTo);
    				Log::debug('Google did not find the zip code entered.');
    				die();
    			}
    		}
    	}
        if(!$geocodeArray){
            Helpers::errorMessage($replyTo);
            Log::debug('Drizzle did not recieve valid location data from google.');
            die();
        }
        if($geocodeArray['status'] != 'OK'){
    		Helpers::errorMessage($replyTo);
    		Log::debug('Drizzle did not recieve valid location data from google.');
    		die();
    	}
        $lat = $geocodeArray['results'][0]['geometry']['location']['lat'];
        $lon = $geocodeArray['results'][0]['geometry']['location']['lng'];
        $locationName = $geocodeArray['results'][0]['formatted_address'];
        Cache::set($zip, [$lat, $lon, $locationName], 90);
        $location = [$lat, $lon, $locationName];
        return $location;
	}

	public static function findKeyword($body)
	{
		preg_match('~\b(help|extended|current)\b~i', $body, $matches);
		$keyword = isset($matches[1]) ? $matches[1] : null;
		return $keyword;
	}

	public static function selectMessage($keyword = null, $zip, $replyTo)
	{
		switch ($keyword) {
    		case 'help':
        	return Helpers::helpMessage($replyTo);
       		 break;
		    case 'current':
		        return Helpers::getCurrentForecast($zip, $replyTo);
		        break;
		    case 'extended':
	        	return Helpers::getExtendedForecast($zip, $replyTo);
		        break;   
		    default:
		        return Helpers::getCurrentForecast($zip, $replyTo);
			}
	}

	public static function getWeather($zip, $replyTo)
    {	$darkSkyApi = env('DARKSKY_API_KEY');
    	$geocodeData = Helpers::getLocation($zip, $replyTo);
    	// Is valid data returned? Return false if not.
    	// dd($geocodeData);
        if(!$geocodeData){
            Helpers::helpMessage($replyTo);
            Log::debug('Unable to retreive valid location data.');
            die();
        }
        $lat = $geocodeData[0];
        $lon = $geocodeData[1];
        $client = new GuzzleClient();
        try {
        	$res = $client->request('GET', 'https://api.darksky.net/forecast/' . $darkSkyApi . '/'.$lat.','.$lon);	
        	// Check for error.
        } catch (GuzzleException $e) {
        	Log::debug((array) $e->getMessage());
        	return false;
        }
        $response = (string) $res->getBody();
        $weatherArray = json_decode($response, true);
        return $weatherArray;
    }

	public static function getCurrentForecast($zip, $replyTo){
		$cache_key = 'current-'.$zip;
		if(Cache::has($cache_key)){
			$replyBody = Cache::get($cache_key);
		}
		$geocodeData = Helpers::getLocation($zip, $replyTo);
	    $weatherArray = Helpers::getWeather($zip, $replyTo);
        $dailyWeather = $weatherArray['daily']['data'];
        $location = $geocodeData[2]; 
	    $forecast = "";
        	$forecast .= date('D M d', $dailyWeather[0]['time']) . ' ';
        	$forecast .= "High ".round($dailyWeather[0]['temperatureHigh']) ."/Low ".round($dailyWeather[0]['temperatureLow']) . ' ';
        	$forecast .= $dailyWeather[0]['summary'];
    	$replyBody = "Forecast for " . $location . " " . $forecast;
    	Cache::set($cache_key, $replyBody, 10);
    	Helpers::sendMessage($replyTo, $replyBody);
		// Log::debug('TESTMODE::Drizzle will send current forecast to' . $replyTo . ' with ' . $forecast);
	}

	public static function getExtendedForecast($zip, $replyTo)
	{
		$cache_key = 'extended-'.$zip;
		if(Cache::has($cache_key)){
			$replyBody = Cache::get($cache_key);
		}
        $geocodeData = Helpers::getLocation($zip, $replyTo);
	    $weatherArray = Helpers::getWeather($zip, $replyTo); 
        $dailyWeather = $weatherArray['daily']['data'];
        // do this in getLocation() and pass or cache, third parameter is null by default when passing, if null grab data 
        $forecast = "";
        for($row = 0; $row < 3; $row++){
        	$forecast .= '***' . date('D M d', $dailyWeather[$row]['time']) . ' ';
        	$forecast .= "High ".round($dailyWeather[$row]['temperatureHigh']) ."/Low ".round($dailyWeather[$row]['temperatureLow']) . ' ';
        	$forecast .= $dailyWeather[$row]['summary'];
        }
        $location = $geocodeData[2];
		$replyBody = "Forecast for ".$location.$forecast; 
		Cache::set($cache_key, $replyBody, 10);
		Helpers::sendMessage($replyTo, $replyBody); 
	}

    public static function sendMessage($replyTo, $replyBody)
    {
    	if (Helpers::characterCount($replyBody)){
    		$account_sid = env('TWILIO_ACCOUNT_SID');
	        $auth_token = env('TWILIO_AUTH_TOKEN');
	        $twilio_number = env('TWILIO_NUMBER');
			$client = new TwilioClient($account_sid, $auth_token);
			$success = $client->messages->create(
			    $replyTo,
			    array(
			        'from' => $twilio_number,
			        'body' => $replyBody
			    		)
			);   
			Log::debug('Sending SMS Msg to: ' . $replyTo . ' Body: ' . $replyBody); 	
			// Log::debug('Client Msg Create Response: ', (array) $success);
			return;
			die();
    	}
    	Log::debug('Response excedeeded the character limit.' . $replyTo);
	}

	public static function helpMessage($replyTo)
	{
		$replyBody = 'To get the current forecast for today, simply enter your zip code. For a 3 Day forecast enter your zip code and "extended". Enjoy your weather texts from Drizzle! =)';
		Helpers::sendMessage($replyTo, $replyBody);
		Log::debug($replyTo.' has accessed the help menu.');
	}

	public static function errorMessage($replyTo)
	{
		$replyBody = 'Drizzle is unable to find a forecast for you. Text "help" for instructions.';
		Helpers::sendMessage($replyTo, $replyBody);
		Log::debug($replyTo.' recieved the error message.');
	}

	public static function characterCount($message){
		$characterCount = strlen($message);
		if ($characterCount <= 480){
			return true;
		}
		Log::debug('A message was too long to send: ' . $message);
		return false;
	}
















}
 
