<?php




function CAS_autoload($class)
{
        static $include_path;
        if (substr($class, 0, 4) !== 'CAS_') {
        return false;
    }
        if (empty($include_path)) {
        $include_path = array(dirname(dirname(__FILE__)), dirname(dirname(__FILE__)) . '/../test/' );
    }

    
    foreach ($include_path as $path) {
        $file_path = $path . '/' . str_replace('_', '/', $class) . '.php';
        $fp = @fopen($file_path, 'r', true);
        if ($fp) {
            fclose($fp);
            include $file_path;
            if (!class_exists($class, false) && !interface_exists($class, false)) {
                die(
                    new Exception(
                        'Class ' . $class . ' was not present in ' .
                        $file_path .
                        ' [CAS_autoload]'
                    )
                );
            }
            return true;
        }
    }
    $e = new Exception(
        'Class ' . $class . ' could not be loaded from ' .
        $file_path . ', file does not exist (Path="'
        . implode(':', $include_path) .'") [CAS_autoload]'
    );
    $trace = $e->getTrace();
    if (isset($trace[2]) && isset($trace[2]['function'])
        && in_array($trace[2]['function'], array('class_exists', 'interface_exists'))
    ) {
        return false;
    }
    if (isset($trace[1]) && isset($trace[1]['function'])
        && in_array($trace[1]['function'], array('class_exists', 'interface_exists'))
    ) {
        return false;
    }
    die ((string) $e);
}

if (function_exists('spl_autoload_register')) {
    if (!(spl_autoload_functions())
        || !in_array('CAS_autoload', spl_autoload_functions())
    ) {
        spl_autoload_register('CAS_autoload');
        if (function_exists('__autoload')
            && !in_array('__autoload', spl_autoload_functions())
        ) {
                                    spl_autoload_register('__autoload');
        }
    }
} elseif (!function_exists('__autoload')) {

    
    function __autoload($class)
    {
        return CAS_autoload($class);
    }
}

?>