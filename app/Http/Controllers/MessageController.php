<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class MessageController extends Controller
{
    public function sendMessage()
    {
    	$account_sid = env('TWILIO_ACCOUNT_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        $twilio_number = env('TWILIO_NUMBER');
		
		$client = new Client($account_sid, $auth_token);
		$client->messages->create(
		    // Where to send a text message (your cell phone?)
		    '+12313424558',
		    array(
		        'from' => $twilio_number,
		        'body' => 'Show me your balls.'
		    		)
				);    	

    }

    





























}
