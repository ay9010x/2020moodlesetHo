<?php

class Horde_Exception_Pear extends Horde_Exception
{
    
    static protected $_class = __CLASS__;

    
    public function __construct(PEAR_Error $error)
    {
        parent::__construct($error->getMessage(), $error->getCode());
        $this->details = $this->_getPearTrace($error);
    }

    
    private function _getPearTrace(PEAR_Error $error)
    {
        $pear_error = '';
        $backtrace = $error->getBacktrace();
        if (!empty($backtrace)) {
            $pear_error .= 'PEAR backtrace:' . "\n\n";
            foreach ($backtrace as $frame) {
                $pear_error .=
                      (isset($frame['class']) ? $frame['class'] : '')
                    . (isset($frame['type']) ? $frame['type'] : '')
                    . (isset($frame['function']) ? $frame['function'] : 'unkown') . ' '
                    . (isset($frame['file']) ? $frame['file'] : 'unkown') . ':'
                    . (isset($frame['line']) ? $frame['line'] : 'unkown') . "\n";
            }
        }
        $userinfo = $error->getUserInfo();
        if (!empty($userinfo)) {
            $pear_error .= "\n" . 'PEAR user info:' . "\n\n";
            if (is_string($userinfo)) {
                $pear_error .= $userinfo;
            } else {
                $pear_error .= print_r($userinfo, true);
            }
        }
        return $pear_error;
    }

    
    static public function catchError($result)
    {
        if ($result instanceOf PEAR_Error) {
            throw new self::$_class($result);
        }
        return $result;
    }
}
