<?php





class PEAR_Exception extends Exception
{
    const OBSERVER_PRINT = -2;
    const OBSERVER_TRIGGER = -4;
    const OBSERVER_DIE = -8;
    protected $cause;
    private static $_observers = array();
    private static $_uniqueid = 0;
    private $_trace;

    
    public function __construct($message, $p2 = null, $p3 = null)
    {
        if (is_int($p2)) {
            $code = $p2;
            $this->cause = null;
        } elseif (is_object($p2) || is_array($p2)) {
                        if (is_object($p2) && !($p2 instanceof Exception)) {
                if (!class_exists('PEAR_Error') || !($p2 instanceof PEAR_Error)) {
                    throw new PEAR_Exception('exception cause must be Exception, ' .
                        'array, or PEAR_Error');
                }
            }
            $code = $p3;
            if (is_array($p2) && isset($p2['message'])) {
                                $p2 = array($p2);
            }
            $this->cause = $p2;
        } else {
            $code = null;
            $this->cause = null;
        }
        parent::__construct($message, $code);
        $this->signal();
    }

    
    public static function addObserver($callback, $label = 'default')
    {
        self::$_observers[$label] = $callback;
    }

    public static function removeObserver($label = 'default')
    {
        unset(self::$_observers[$label]);
    }

    
    public static function getUniqueId()
    {
        return self::$_uniqueid++;
    }

    private function signal()
    {
        foreach (self::$_observers as $func) {
            if (is_callable($func)) {
                call_user_func($func, $this);
                continue;
            }
            settype($func, 'array');
            switch ($func[0]) {
                case self::OBSERVER_PRINT :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    printf($f, $this->getMessage());
                    break;
                case self::OBSERVER_TRIGGER :
                    $f = (isset($func[1])) ? $func[1] : E_USER_NOTICE;
                    trigger_error($this->getMessage(), $f);
                    break;
                case self::OBSERVER_DIE :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    die(printf($f, $this->getMessage()));
                    break;
                default:
                    trigger_error('invalid observer type', E_USER_WARNING);
            }
        }
    }

    
    public function getErrorData()
    {
        return array();
    }

    
    public function getCause()
    {
        return $this->cause;
    }

    
    public function getCauseMessage(&$causes)
    {
        $trace = $this->getTraceSafe();
        $cause = array('class'   => get_class($this),
                       'message' => $this->message,
                       'file' => 'unknown',
                       'line' => 'unknown');
        if (isset($trace[0])) {
            if (isset($trace[0]['file'])) {
                $cause['file'] = $trace[0]['file'];
                $cause['line'] = $trace[0]['line'];
            }
        }
        $causes[] = $cause;
        if ($this->cause instanceof PEAR_Exception) {
            $this->cause->getCauseMessage($causes);
        } elseif ($this->cause instanceof Exception) {
            $causes[] = array('class'   => get_class($this->cause),
                              'message' => $this->cause->getMessage(),
                              'file' => $this->cause->getFile(),
                              'line' => $this->cause->getLine());
        } elseif (class_exists('PEAR_Error') && $this->cause instanceof PEAR_Error) {
            $causes[] = array('class' => get_class($this->cause),
                              'message' => $this->cause->getMessage(),
                              'file' => 'unknown',
                              'line' => 'unknown');
        } elseif (is_array($this->cause)) {
            foreach ($this->cause as $cause) {
                if ($cause instanceof PEAR_Exception) {
                    $cause->getCauseMessage($causes);
                } elseif ($cause instanceof Exception) {
                    $causes[] = array('class'   => get_class($cause),
                                   'message' => $cause->getMessage(),
                                   'file' => $cause->getFile(),
                                   'line' => $cause->getLine());
                } elseif (class_exists('PEAR_Error') && $cause instanceof PEAR_Error) {
                    $causes[] = array('class' => get_class($cause),
                                      'message' => $cause->getMessage(),
                                      'file' => 'unknown',
                                      'line' => 'unknown');
                } elseif (is_array($cause) && isset($cause['message'])) {
                                        $causes[] = array(
                        'class' => $cause['package'],
                        'message' => $cause['message'],
                        'file' => isset($cause['context']['file']) ?
                                            $cause['context']['file'] :
                                            'unknown',
                        'line' => isset($cause['context']['line']) ?
                                            $cause['context']['line'] :
                                            'unknown',
                    );
                }
            }
        }
    }

    public function getTraceSafe()
    {
        if (!isset($this->_trace)) {
            $this->_trace = $this->getTrace();
            if (empty($this->_trace)) {
                $backtrace = debug_backtrace();
                $this->_trace = array($backtrace[count($backtrace)-1]);
            }
        }
        return $this->_trace;
    }

    public function getErrorClass()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['class'];
    }

    public function getErrorMethod()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['function'];
    }

    public function __toString()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return $this->toHtml();
        }
        return $this->toText();
    }

    public function toHtml()
    {
        $trace = $this->getTraceSafe();
        $causes = array();
        $this->getCauseMessage($causes);
        $html =  '<table style="border: 1px" cellspacing="0">' . "\n";
        foreach ($causes as $i => $cause) {
            $html .= '<tr><td colspan="3" style="background: #ff9999">'
               . str_repeat('-', $i) . ' <b>' . $cause['class'] . '</b>: '
               . htmlspecialchars($cause['message']) . ' in <b>' . $cause['file'] . '</b> '
               . 'on line <b>' . $cause['line'] . '</b>'
               . "</td></tr>\n";
        }
        $html .= '<tr><td colspan="3" style="background-color: #aaaaaa; text-align: center; font-weight: bold;">Exception trace</td></tr>' . "\n"
               . '<tr><td style="text-align: center; background: #cccccc; width:20px; font-weight: bold;">#</td>'
               . '<td style="text-align: center; background: #cccccc; font-weight: bold;">Function</td>'
               . '<td style="text-align: center; background: #cccccc; font-weight: bold;">Location</td></tr>' . "\n";

        foreach ($trace as $k => $v) {
            $html .= '<tr><td style="text-align: center;">' . $k . '</td>'
                   . '<td>';
            if (!empty($v['class'])) {
                $html .= $v['class'] . $v['type'];
            }
            $html .= $v['function'];
            $args = array();
            if (!empty($v['args'])) {
                foreach ($v['args'] as $arg) {
                    if (is_null($arg)) $args[] = 'null';
                    elseif (is_array($arg)) $args[] = 'Array';
                    elseif (is_object($arg)) $args[] = 'Object('.get_class($arg).')';
                    elseif (is_bool($arg)) $args[] = $arg ? 'true' : 'false';
                    elseif (is_int($arg) || is_double($arg)) $args[] = $arg;
                    else {
                        $arg = (string)$arg;
                        $str = htmlspecialchars(substr($arg, 0, 16));
                        if (strlen($arg) > 16) $str .= '&hellip;';
                        $args[] = "'" . $str . "'";
                    }
                }
            }
            $html .= '(' . implode(', ',$args) . ')'
                   . '</td>'
                   . '<td>' . (isset($v['file']) ? $v['file'] : 'unknown')
                   . ':' . (isset($v['line']) ? $v['line'] : 'unknown')
                   . '</td></tr>' . "\n";
        }
        $html .= '<tr><td style="text-align: center;">' . ($k+1) . '</td>'
               . '<td>{main}</td>'
               . '<td>&nbsp;</td></tr>' . "\n"
               . '</table>';
        return $html;
    }

    public function toText()
    {
        $causes = array();
        $this->getCauseMessage($causes);
        $causeMsg = '';
        foreach ($causes as $i => $cause) {
            $causeMsg .= str_repeat(' ', $i) . $cause['class'] . ': '
                   . $cause['message'] . ' in ' . $cause['file']
                   . ' on line ' . $cause['line'] . "\n";
        }
        return $causeMsg . $this->getTraceAsString();
    }
}