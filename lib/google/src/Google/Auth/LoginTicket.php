<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Auth_LoginTicket
{
  const USER_ATTR = "sub";

    private $envelope;

    private $payload;

  
  public function __construct($envelope, $payload)
  {
    $this->envelope = $envelope;
    $this->payload = $payload;
  }

  
  public function getUserId()
  {
    if (array_key_exists(self::USER_ATTR, $this->payload)) {
      return $this->payload[self::USER_ATTR];
    }
    throw new Google_Auth_Exception("No user_id in token");
  }

  
  public function getAttributes()
  {
    return array("envelope" => $this->envelope, "payload" => $this->payload);
  }
}
