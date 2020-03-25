<?php


namespace core\update;

defined('MOODLE_INTERNAL') || die();


class info {

    
    public $component;
    
    public $version;
    
    public $release = null;
    
    public $maturity = null;
    
    public $url = null;
    
    public $download = null;
    
    public $downloadmd5 = null;

    
    public function __construct($name, array $info) {
        $this->component = $name;
        foreach ($info as $k => $v) {
            if (property_exists('\core\update\info', $k) and $k != 'component') {
                $this->$k = $v;
            }
        }
    }
}
