<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


abstract class Google_Auth_Abstract
{
  
  abstract public function authenticatedRequest(Google_Http_Request $request);
  abstract public function sign(Google_Http_Request $request);
}
