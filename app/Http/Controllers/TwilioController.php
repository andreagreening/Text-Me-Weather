<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services_Twilio;

class TwilioController extends Controller
{
     use InteractsWithQueue;
    private $twilioClient;

    /**
     * Create the event handler.
     *
     */
    public function __construct()
    {
        $this->twilioClient = new Services_Twilio(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
    }
}
