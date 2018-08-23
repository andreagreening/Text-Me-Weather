<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;
use Twilio\Rest\Client as TwilioClient;
use Log;
use Validator;
use Helpers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function twilio(Request $request)
    {
        // Check how many times a number has texted in the past day, add limits to prevent being bombarded by 1000s of texts from one number.
        // // If this is a phone call, say something
        if(!$request->has('SmsMessageSid')){
            $caller = $request->get('From');
            Log::debug($caller.' has attempted to call.');
        //     // Eventually have an audio message that informs the caller to text me!
            die();
        }
        // TODO::If this is an SMS, decode message. If just zip, send daily forecast. If zip and "extended", then send 3 day forecast. If "help", send help menu. 
        $replyTo = $request->get('From');
        $smsBody = $request->get('Body');
        $smsBody = strtolower($smsBody);
        $findZip = Helpers::findZip($smsBody, $replyTo); 
        $zip = implode(" ", $findZip);
        $keyword = Helpers::findKeyword($smsBody);
        Helpers::selectMessage($keyword, $zip, $replyTo);
        
    }

    public function test()
    {
        $smsBody = "jibber jabberish 49616";
        $replyTo = '+12313424558';

        $smsBody = strtolower($smsBody);
        $findZip = Helpers::findZip($smsBody, $replyTo);
        $zip = implode(" ", $findZip);
        $keyword = Helpers::findKeyword($smsBody);
        $message = Helpers::selectMessage($keyword, $zip, $replyTo);
       
        if(! $message){
           // Log::debug('No Message Returned, error message would send.');
            die();
        }
       dd($message);
    }
























}
