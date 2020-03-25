<?php



namespace core\output;


class mustache_javascript_helper {

    
    private $requires = null;

    
    public function __construct($requires) {
        $this->requires = $requires;
    }

    
    public function help($text, \Mustache_LambdaHelper $helper) {
        $this->requires->js_amd_inline($helper->render($text));
    }
}
