<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class Google_Auth_Exception extends Google_Exception
{
}
