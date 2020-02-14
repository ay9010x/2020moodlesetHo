<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


interface Google_Task_Retryable
{
  
  public function allowedRetries();
}
