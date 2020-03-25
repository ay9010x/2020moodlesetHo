<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Logger_File extends Google_Logger_Abstract
{
  
  private $file;
  
  private $mode = 0640;
  
  private $lock = false;

  
  private $trappedErrorNumber;
  
  private $trappedErrorString;

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);

    $file = $client->getClassConfig('Google_Logger_File', 'file');
    if (!is_string($file) && !is_resource($file)) {
      throw new Google_Logger_Exception(
          'File logger requires a filename or a valid file pointer'
      );
    }

    $mode = $client->getClassConfig('Google_Logger_File', 'mode');
    if (!$mode) {
      $this->mode = $mode;
    }

    $this->lock = (bool) $client->getClassConfig('Google_Logger_File', 'lock');
    $this->file = $file;
  }

  
  protected function write($message)
  {
    if (is_string($this->file)) {
      $this->open();
    } elseif (!is_resource($this->file)) {
      throw new Google_Logger_Exception('File pointer is no longer available');
    }

    if ($this->lock) {
      flock($this->file, LOCK_EX);
    }

    fwrite($this->file, (string) $message);

    if ($this->lock) {
      flock($this->file, LOCK_UN);
    }
  }

  
  private function open()
  {
        $this->trappedErrorNumber = null;
    $this->trappedErrorString = null;

    $old = set_error_handler(array($this, 'trapError'));

    $needsChmod = !file_exists($this->file);
    $fh = fopen($this->file, 'a');

    restore_error_handler();

        if ($this->trappedErrorNumber) {
      throw new Google_Logger_Exception(
          sprintf(
              "Logger Error: '%s'",
              $this->trappedErrorString
          ),
          $this->trappedErrorNumber
      );
    }

    if ($needsChmod) {
      @chmod($this->file, $this->mode & ~umask());
    }

    return $this->file = $fh;
  }

  
  private function close()
  {
    if (is_resource($this->file)) {
      fclose($this->file);
    }
  }

  
  private function trapError($errno, $errstr)
  {
    $this->trappedErrorNumber = $errno;
    $this->trappedErrorString = $errstr;
  }

  public function __destruct()
  {
    $this->close();
  }
}
