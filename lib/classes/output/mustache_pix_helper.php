<?php



namespace core\output;

use Mustache_LambdaHelper;
use renderer_base;


class mustache_pix_helper {

    
    private $renderer;

    
    public function __construct(renderer_base $renderer) {
        $this->renderer = $renderer;
    }

    
    public function pix($text, Mustache_LambdaHelper $helper) {
                $key = strtok($text, ",");
        $key = trim($key);
        $component = strtok(",");
        $component = trim($component);
        if (!$component) {
            $component = '';
        }
        $text = strtok("");
                $text = $helper->render($text);

        return trim($this->renderer->pix_icon($key, $text, $component));
    }
}

