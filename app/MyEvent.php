<?php

namespace App;

class MyEvent
{
    /**
     * Create a new class instance.
     */
    public $message;
    public function __construct($message)
    {
        $this->message = $message;
    }
    public function broadcastOn()
  {
      return ['my-channel'];
  }
  public function broadcastAs()
  {
      return 'my-event';
  }
}
