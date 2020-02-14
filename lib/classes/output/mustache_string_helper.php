<?php



namespace core\output;

use Mustache_LambdaHelper;
use stdClass;


class mustache_string_helper {

    
    public function str($text, Mustache_LambdaHelper $helper) {
                $key = strtok($text, ",");
        $key = trim($key);
        $component = strtok(",");
        $component = trim($component);
        if (!$component) {
            $component = '';
        }

        $a = new stdClass();

        $next = strtok('');
        $next = trim($next);
        if ((strpos($next, '{') === 0) && (strpos($next, '{{') !== 0)) {
            $rawjson = $helper->render($next);
            $a = json_decode($rawjson);
        } else {
            $a = $helper->render($next);
        }
        return get_string($key, $component, $a);
    }
}
