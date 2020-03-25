<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class Google_Service_Exception extends Google_Exception implements Google_Task_Retryable
{
  
  protected $errors = array();

  
  private $retryMap = array();

  
  public function __construct(
      $message,
      $code = 0,
      Exception $previous = null,
      $errors = array(),
      array $retryMap = null
  ) {
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      parent::__construct($message, $code, $previous);
    } else {
      parent::__construct($message, $code);
    }

    $this->errors = $errors;

    if (is_array($retryMap)) {
      $this->retryMap = $retryMap;
    }
  }

  
  public function getErrors()
  {
    return $this->errors;
  }

  
  public function allowedRetries()
  {
    if (isset($this->retryMap[$this->code])) {
      return $this->retryMap[$this->code];
    }

    $errors = $this->getErrors();

    if (!empty($errors) && isset($errors[0]['reason']) &&
        isset($this->retryMap[$errors[0]['reason']])) {
      return $this->retryMap[$errors[0]['reason']];
    }

    return 0;
  }
}
