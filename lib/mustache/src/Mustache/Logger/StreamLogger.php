<?php




class Mustache_Logger_StreamLogger extends Mustache_Logger_AbstractLogger
{
    protected static $levels = array(
        self::DEBUG     => 100,
        self::INFO      => 200,
        self::NOTICE    => 250,
        self::WARNING   => 300,
        self::ERROR     => 400,
        self::CRITICAL  => 500,
        self::ALERT     => 550,
        self::EMERGENCY => 600,
    );

    protected $level;
    protected $stream = null;
    protected $url    = null;

    
    public function __construct($stream, $level = Mustache_Logger::ERROR)
    {
        $this->setLevel($level);

        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->url = $stream;
        }
    }

    
    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    
    public function setLevel($level)
    {
        if (!array_key_exists($level, self::$levels)) {
            throw new Mustache_Exception_InvalidArgumentException(sprintf('Unexpected logging level: %s', $level));
        }

        $this->level = $level;
    }

    
    public function getLevel()
    {
        return $this->level;
    }

    
    public function log($level, $message, array $context = array())
    {
        if (!array_key_exists($level, self::$levels)) {
            throw new Mustache_Exception_InvalidArgumentException(sprintf('Unexpected logging level: %s', $level));
        }

        if (self::$levels[$level] >= self::$levels[$this->level]) {
            $this->writeLog($level, $message, $context);
        }
    }

    
    protected function writeLog($level, $message, array $context = array())
    {
        if (!is_resource($this->stream)) {
            if (!isset($this->url)) {
                throw new Mustache_Exception_LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }

            $this->stream = fopen($this->url, 'a');
            if (!is_resource($this->stream)) {
                                throw new Mustache_Exception_RuntimeException(sprintf('The stream or file "%s" could not be opened.', $this->url));
                            }
        }

        fwrite($this->stream, self::formatLine($level, $message, $context));
    }

    
    protected static function getLevelName($level)
    {
        return strtoupper($level);
    }

    
    protected static function formatLine($level, $message, array $context = array())
    {
        return sprintf(
            "%s: %s\n",
            self::getLevelName($level),
            self::interpolateMessage($message, $context)
        );
    }

    
    protected static function interpolateMessage($message, array $context = array())
    {
        if (strpos($message, '{') === false) {
            return $message;
        }

                $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

                return strtr($message, $replace);
    }
}
