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
        $account_sid = 'ACeaba3e0d757c54dddf756514c2006400';
        $auth_token = '1e52a0d9aceda2b86764d9410b3b344d';
        $this->twilioClient = new Services_Twilio($account_sid, $auth_token);
    }
}
