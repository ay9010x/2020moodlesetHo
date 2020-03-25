<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Logger_Psr extends Google_Logger_Abstract
{
  
  private $logger;

  
  public function __construct(Google_Client $client,  $logger = null)
  {
    parent::__construct($client);

    if ($logger) {
      $this->setLogger($logger);
    }
  }

  
  public function setLogger( $logger)
  {
    $this->logger = $logger;
  }

  
  public function shouldHandle($level)
  {
    return isset($this->logger) && parent::shouldHandle($level);
  }

  
  public function log($level, $message, array $context = array())
  {
    if (!$this->shouldHandle($level)) {
      return false;
    }

    if ($context) {
      $this->reverseJsonInContext($context);
    }

    $levelName = is_int($level) ? array_search($level, self::$levels) : $level;
    $this->logger->log($levelName, $message, $context);
  }

  
  protected function write($message, array $context = array())
  {
  }
}
