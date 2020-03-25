<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Logger_Null extends Google_Logger_Abstract
{
  
  public function shouldHandle($level)
  {
    return false;
  }

  
  protected function write($message, array $context = array())
  {
  }
}
