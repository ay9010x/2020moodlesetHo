<?php 

namespace MrClay;

use MrClay\Cli\Arg;
use InvalidArgumentException;


class Cli {
    
    
    public $errors = array();
    
    
    public $values = array();

    
    public $moreArgs = array();

    
    public $debug = array();

    
    public $isHelpRequest = false;

    
    protected $_args = array();

    
    protected $_stdin = null;

    
    protected $_stdout = null;
    
    
    public function __construct($exitIfNoStdin = true)
    {
        if ($exitIfNoStdin && ! defined('STDIN')) {
            exit('This script is for command-line use only.');
        }
        if (isset($GLOBALS['argv'][1])
             && ($GLOBALS['argv'][1] === '-?' || $GLOBALS['argv'][1] === '--help')) {
            $this->isHelpRequest = true;
        }
    }

    
    public function addOptionalArg($letter)
    {
        return $this->addArgument($letter, false);
    }

    
    public function addRequiredArg($letter)
    {
        return $this->addArgument($letter, true);
    }

    
    public function addArgument($letter, $required, Arg $arg = null)
    {
        if (! preg_match('/^[a-zA-Z]$/', $letter)) {
            throw new InvalidArgumentException('$letter must be in [a-zA-Z]');
        }
        if (! $arg) {
            $arg = new Arg($required);
        }
        $this->_args[$letter] = $arg;
        return $arg;
    }

    
    public function getArgument($letter)
    {
        return isset($this->_args[$letter]) ? $this->_args[$letter] : null;
    }

    
    public function validate()
    {
        $options = '';
        $this->errors = array();
        $this->values = array();
        $this->_stdin = null;
        
        if ($this->isHelpRequest) {
            return false;
        }
        
        $lettersUsed = '';
        foreach ($this->_args as $letter => $arg) {
            
            $options .= $letter;
            $lettersUsed .= $letter;
            
            if ($arg->mayHaveValue || $arg->mustHaveValue) {
                $options .= ($arg->mustHaveValue ? ':' : '::');
            }
        }

        $this->debug['argv'] = $GLOBALS['argv'];
        $argvCopy = array_slice($GLOBALS['argv'], 1);
        $o = getopt($options);
        $this->debug['getopt_options'] = $options;
        $this->debug['getopt_return'] = $o;

        foreach ($this->_args as $letter => $arg) {
            
            $this->values[$letter] = false;
            if (isset($o[$letter])) {
                if (is_bool($o[$letter])) {

                                        $k = array_search("-$letter", $argvCopy);
                    if ($k !== false) {
                        array_splice($argvCopy, $k, 1);
                    }

                    if ($arg->mustHaveValue) {
                        $this->addError($letter, "Missing value");
                    } else {
                        $this->values[$letter] = true;
                    }
                } else {
                                        $this->values[$letter] = $o[$letter];
                    $v =& $this->values[$letter];

                                                            $pattern = "/^-{$letter}=?" . preg_quote($v, '/') . "$/";
                    $foundInArgv = false;
                    foreach ($argvCopy as $k => $argV) {
                        if (preg_match($pattern, $argV)) {
                            array_splice($argvCopy, $k, 1);
                            $foundInArgv = true;
                            break;
                        }
                    }
                    if (! $foundInArgv) {
                                                $k = array_search("-$letter", $argvCopy);
                        if ($k !== false) {
                            array_splice($argvCopy, $k, 2);
                        }
                    }
                    
                                        if (strlen($lettersUsed) > 1) {
                        $pattern = "/^-[" . str_replace($letter, '', $lettersUsed) . "]/i";
                        if (preg_match($pattern, $v)) {
                            $this->addError($letter, "Value was read as another option: %s", $v);
                            return false;
                        }    
                    }
                    if ($arg->assertFile || $arg->assertDir) {
                        if ($v[0] !== '/' && $v[0] !== '~') {
                            $this->values["$letter.raw"] = $v;
                            $v = getcwd() . "/$v";
                        }
                    }
                    if ($arg->assertFile) {
                        if ($arg->useAsInfile) {
                            $this->_stdin = $v;
                        } elseif ($arg->useAsOutfile) {
                            $this->_stdout = $v;
                        }
                        if ($arg->assertReadable && ! is_readable($v)) {
                            $this->addError($letter, "File not readable: %s", $v);
                            continue;
                        }
                        if ($arg->assertWritable) {
                            if (is_file($v)) {
                                if (! is_writable($v)) {
                                    $this->addError($letter, "File not writable: %s", $v);
                                }
                            } else {
                                if (! is_writable(dirname($v))) {
                                    $this->addError($letter, "Directory not writable: %s", dirname($v));
                                }
                            }
                        }
                    } elseif ($arg->assertDir && $arg->assertWritable && ! is_writable($v)) {
                        $this->addError($letter, "Directory not readable: %s", $v);
                    }
                }
            } else {
                if ($arg->isRequired()) {
                    $this->addError($letter, "Missing");
                }
            }
        }
        $this->moreArgs = $argvCopy;
        reset($this->moreArgs);
        return empty($this->errors);
    }

    
    public function getPathArgs()
    {
        $r = $this->moreArgs;
        foreach ($r as $k => $v) {
            if ($v[0] !== '/' && $v[0] !== '~') {
                $v = getcwd() . "/$v";
                $v = str_replace('/./', '/', $v);
                do {
                    $v = preg_replace('@/[^/]+/\\.\\./@', '/', $v, 1, $changed);
                } while ($changed);
                $r[$k] = $v;
            }
        }
        return $r;
    }
    
    
    public function getErrorReport()
    {
        if (empty($this->errors)) {
            return '';
        }
        $r = "Some arguments did not pass validation:\n";
        foreach ($this->errors as $letter => $arr) {
            $r .= "  $letter : " . implode(', ', $arr) . "\n";
        }
        $r .= "\n";
        return $r;
    }

    
    public function getArgumentsListing()
    {
        $r = "\n";
        foreach ($this->_args as $letter => $arg) {
            
            $desc = $arg->getDescription();
            $flag = " -$letter ";
            if ($arg->mayHaveValue) {
                $flag .= "[VAL]";
            } elseif ($arg->mustHaveValue) {
                $flag .= "VAL";
            }
            if ($arg->assertFile) {
                $flag = str_replace('VAL', 'FILE', $flag);
            } elseif ($arg->assertDir) {
                $flag = str_replace('VAL', 'DIR', $flag);
            }
            if ($arg->isRequired()) {
                $desc = "(required) $desc";
            }
            $flag = str_pad($flag, 12, " ", STR_PAD_RIGHT);
            $desc = wordwrap($desc, 70);
            $r .= $flag . str_replace("\n", "\n            ", $desc) . "\n\n";
        }
        return $r;
    }
    
    
    public function openInput()
    {
        if (null === $this->_stdin) {
            return STDIN;
        } else {
            $this->_stdin = fopen($this->_stdin, 'rb');
            return $this->_stdin;
        }
    }
    
    public function closeInput()
    {
        if (null !== $this->_stdin) {
            fclose($this->_stdin);
        }
    }
    
    
    public function openOutput()
    {
        if (null === $this->_stdout) {
            return STDOUT;
        } else {
            $this->_stdout = fopen($this->_stdout, 'wb');
            return $this->_stdout;
        }
    }
    
    public function closeOutput()
    {
        if (null !== $this->_stdout) {
            fclose($this->_stdout);
        }
    }

    
    protected function addError($letter, $msg, $value = null)
    {
        if ($value !== null) {
            $value = var_export($value, 1);
        }
        $this->errors[$letter][] = sprintf($msg, $value);
    }
}

