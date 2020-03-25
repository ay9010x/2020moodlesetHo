<?php





class Hint_ResultPrinter extends PHPUnit_TextUI_ResultPrinter {
    public function __construct() {
                if (defined('DEBUG_BACKTRACE_PROVIDE_OBJECT')) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
            if (isset($backtrace[2]['object']) and ($backtrace[2]['object'] instanceof PHPUnit_TextUI_Command)) {
                list($verbose, $colors, $debug) = Hacky_TextUI_Command_reader::get_settings_hackery($backtrace[2]['object']);
                parent::__construct(null, $verbose, $colors, $debug);
                return;
            }
        }
                parent::__construct(null, false, self::COLOR_DEFAULT, false);
    }

    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect) {
        global $CFG;

        parent::printDefectTrace($defect);

        $failedTest = $defect->failedTest();
        $testName = get_class($failedTest);

        $exception = $defect->thrownException();
        $trace = $exception->getTrace();

        if (class_exists('ReflectionClass')) {
            $reflection = new ReflectionClass($testName);
            $file = $reflection->getFileName();

        } else {
            $file = false;
            $dirroot = realpath($CFG->dirroot).DIRECTORY_SEPARATOR;
            $classpath = realpath("$CFG->dirroot/lib/phpunit/classes").DIRECTORY_SEPARATOR;
            foreach ($trace as $item) {
                if (strpos($item['file'], $dirroot) === 0 and strpos($item['file'], $classpath) !== 0) {
                    if ($content = file_get_contents($item['file'])) {
                        if (preg_match('/class\s+'.$testName.'\s+extends/', $content)) {
                            $file = $item['file'];
                            break;
                        }
                    }
                }
            }
        }

        if ($file === false) {
            return;
        }

        $cwd = getcwd();
        if (strpos($file, $cwd) === 0) {
            $file = substr($file, strlen($cwd)+1);
        }

        $executable = null;

        if (isset($_SERVER['argv'][0])) {
            if (preg_match('/phpunit(\.bat|\.cmd)?$/', $_SERVER['argv'][0])) {
                $executable = $_SERVER['argv'][0];
                for($i=1;$i<count($_SERVER['argv']);$i++) {
                    if (!isset($_SERVER['argv'][$i])) {
                        break;
                    }
                    if (in_array($_SERVER['argv'][$i], array('--colors', '--verbose', '-v', '--debug', '--strict'))) {
                        $executable .= ' '.$_SERVER['argv'][$i];
                    }
                }
            }
        }

        if (!$executable) {
            $executable = 'phpunit';
            if (testing_is_cygwin()) {
                $file = str_replace('\\', '/', $file);
                if (!testing_is_mingw()) {
                    $executable = 'phpunit.bat';
                }
            }
        }

        $this->write("\nTo re-run:\n $executable $testName $file\n");
    }
}



class Hacky_TextUI_Command_reader extends PHPUnit_TextUI_Command {
    public static function get_settings_hackery(PHPUnit_TextUI_Command $toread) {
        $arguments = $toread->arguments;
        $config = PHPUnit_Util_Configuration::getInstance($arguments['configuration'])->getPHPUnitConfiguration();

        $verbose = isset($config['verbose']) ? $config['verbose'] : false;
        $verbose = isset($arguments['verbose']) ? $arguments['verbose'] : $verbose;

        $colors = isset($config['colors']) ? $config['colors'] : Hint_ResultPrinter::COLOR_DEFAULT;
        $colors = isset($arguments['colors']) ? $arguments['colors'] : $colors;

        $debug = isset($config['debug']) ? $config['debug'] : false;
        $debug = isset($arguments['debug']) ? $arguments['debug'] : $debug;

        return array($verbose, $colors, $debug);
    }
}
