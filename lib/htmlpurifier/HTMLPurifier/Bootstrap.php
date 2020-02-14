<?php

if (!defined('HTMLPURIFIER_PREFIX')) {
    define('HTMLPURIFIER_PREFIX', realpath(dirname(__FILE__) . '/..'));
}

if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;
        case 'DAR':
            define('PHP_EOL', "\r");
            break;
        default:
            define('PHP_EOL', "\n");
    }
}


class HTMLPurifier_Bootstrap
{

    
    public static function autoload($class)
    {
        $file = HTMLPurifier_Bootstrap::getPath($class);
        if (!$file) {
            return false;
        }
                                                require_once HTMLPURIFIER_PREFIX . '/' . $file;
        return true;
    }

    
    public static function getPath($class)
    {
        if (strncmp('HTMLPurifier', $class, 12) !== 0) {
            return false;
        }
                if (strncmp('HTMLPurifier_Language_', $class, 22) === 0) {
            $code = str_replace('_', '-', substr($class, 22));
            $file = 'HTMLPurifier/Language/classes/' . $code . '.php';
        } else {
            $file = str_replace('_', '/', $class) . '.php';
        }
        if (!file_exists(HTMLPURIFIER_PREFIX . '/' . $file)) {
            return false;
        }
        return $file;
    }

    
    public static function registerAutoload()
    {
        $autoload = array('HTMLPurifier_Bootstrap', 'autoload');
        if (($funcs = spl_autoload_functions()) === false) {
            spl_autoload_register($autoload);
        } elseif (function_exists('spl_autoload_unregister')) {
            if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
                                spl_autoload_register($autoload, true, true);
            } else {
                $buggy  = version_compare(PHP_VERSION, '5.2.11', '<');
                $compat = version_compare(PHP_VERSION, '5.1.2', '<=') &&
                          version_compare(PHP_VERSION, '5.1.0', '>=');
                foreach ($funcs as $func) {
                    if ($buggy && is_array($func)) {
                                                                        $reflector = new ReflectionMethod($func[0], $func[1]);
                        if (!$reflector->isStatic()) {
                            throw new Exception(
                                'HTML Purifier autoloader registrar is not compatible
                                with non-static object methods due to PHP Bug #44144;
                                Please do not use HTMLPurifier.autoload.php (or any
                                file that includes this file); instead, place the code:
                                spl_autoload_register(array(\'HTMLPurifier_Bootstrap\', \'autoload\'))
                                after your own autoloaders.'
                            );
                        }
                                                                        if ($compat) {
                            $func = implode('::', $func);
                        }
                    }
                    spl_autoload_unregister($func);
                }
                spl_autoload_register($autoload);
                foreach ($funcs as $func) {
                    spl_autoload_register($func);
                }
            }
        }
    }
}

