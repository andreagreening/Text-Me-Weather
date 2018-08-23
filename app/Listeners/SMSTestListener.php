<?php

namespace App\Listeners;

use App\Events\SMSTest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SMSTestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SMSTest  $event
     * @return void
     */
    public function handle(SMSTest $event)
    {
        //
    }
}
