<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Task_Runner
{
  
  private $maxDelay = 60;
  
  private $delay = 1;

  
  private $factor = 2;
  
  private $jitter = 0.5;

  
  private $attempts = 0;
  
  private $maxAttempts = 1;

  
  private $client;

  
  private $name;
  
  private $action;
  
  private $arguments;

  
  public function __construct(
      Google_Client $client,
      $name,
      $action,
      array $arguments = array()
  ) {
    $config = (array) $client->getClassConfig('Google_Task_Runner');

    if (isset($config['initial_delay'])) {
      if ($config['initial_delay'] < 0) {
        throw new Google_Task_Exception(
            'Task configuration `initial_delay` must not be negative.'
        );
      }

      $this->delay = $config['initial_delay'];
    }

    if (isset($config['max_delay'])) {
      if ($config['max_delay'] <= 0) {
        throw new Google_Task_Exception(
            'Task configuration `max_delay` must be greater than 0.'
        );
      }

      $this->maxDelay = $config['max_delay'];
    }

    if (isset($config['factor'])) {
      if ($config['factor'] <= 0) {
        throw new Google_Task_Exception(
            'Task configuration `factor` must be greater than 0.'
        );
      }

      $this->factor = $config['factor'];
    }

    if (isset($config['jitter'])) {
      if ($config['jitter'] <= 0) {
        throw new Google_Task_Exception(
            'Task configuration `jitter` must be greater than 0.'
        );
      }

      $this->jitter = $config['jitter'];
    }

    if (isset($config['retries'])) {
      if ($config['retries'] < 0) {
        throw new Google_Task_Exception(
            'Task configuration `retries` must not be negative.'
        );
      }
      $this->maxAttempts += $config['retries'];
    }

    if (!is_callable($action)) {
        throw new Google_Task_Exception(
            'Task argument `$action` must be a valid callable.'
        );
    }

    $this->name = $name;
    $this->client = $client;
    $this->action = $action;
    $this->arguments = $arguments;
  }

  
  public function canAttmpt()
  {
    return $this->attempts < $this->maxAttempts;
  }

  
  public function run()
  {
    while ($this->attempt()) {
      try {
        return call_user_func_array($this->action, $this->arguments);
      } catch (Google_Task_Retryable $exception) {
        $allowedRetries = $exception->allowedRetries();

        if (!$this->canAttmpt() || !$allowedRetries) {
          throw $exception;
        }

        if ($allowedRetries > 0) {
          $this->maxAttempts = min(
              $this->maxAttempts,
              $this->attempts + $allowedRetries
          );
        }
      }
    }
  }

  
  public function attempt()
  {
    if (!$this->canAttmpt()) {
      return false;
    }

    if ($this->attempts > 0) {
      $this->backOff();
    }

    $this->attempts++;
    return true;
  }

  
  private function backOff()
  {
    $delay = $this->getDelay();

    $this->client->getLogger()->debug(
        'Retrying task with backoff',
        array(
            'request' => $this->name,
            'retry' => $this->attempts,
            'backoff_seconds' => $delay
        )
    );

    usleep($delay * 1000000);
  }

  
  private function getDelay()
  {
    $jitter = $this->getJitter();
    $factor = $this->attempts > 1 ? $this->factor + $jitter : 1 + abs($jitter);

    return $this->delay = min($this->maxDelay, $this->delay * $factor);
  }

  
  private function getJitter()
  {
    return $this->jitter * 2 * mt_rand() / mt_getrandmax() - $this->jitter;
  }
}
