<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class Google_IO_Exception extends Google_Exception implements Google_Task_Retryable
{
  
  private $retryMap = array();

  
  public function __construct(
      $message,
      $code = 0,
      Exception $previous = null,
      array $retryMap = null
  ) {
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      parent::__construct($message, $code, $previous);
    } else {
      parent::__construct($message, $code);
    }

    if (is_array($retryMap)) {
      $this->retryMap = $retryMap;
    }
  }

  
  public function allowedRetries()
  {
    if (isset($this->retryMap[$this->code])) {
      return $this->retryMap[$this->code];
    }

    return 0;
  }
}
