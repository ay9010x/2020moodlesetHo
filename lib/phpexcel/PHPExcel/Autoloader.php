<?php

PHPExcel_Autoloader::register();
if (ini_get('mbstring.func_overload') & 2) {
    throw new PHPExcel_Exception('Multibyte function overloading in PHP must be disabled for string functions (2).');
}
PHPExcel_Shared_String::buildCharacterSets();


class PHPExcel_Autoloader
{
    
    public static function register()
    {
        if (function_exists('__autoload')) {
                        spl_autoload_register('__autoload');
        }
                if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            return spl_autoload_register(array('PHPExcel_Autoloader', 'load'), true, true);
        } else {
            return spl_autoload_register(array('PHPExcel_Autoloader', 'load'));
        }
    }

    
    public static function load($pClassName)
    {
        if ((class_exists($pClassName, false)) || (strpos($pClassName, 'PHPExcel') !== 0)) {
                        return false;
        }

        $pClassFilePath = PHPEXCEL_ROOT .
            str_replace('_', DIRECTORY_SEPARATOR, $pClassName) .
            '.php';

        if ((file_exists($pClassFilePath) === false) || (is_readable($pClassFilePath) === false)) {
                        return false;
        }

        require($pClassFilePath);
    }
}
