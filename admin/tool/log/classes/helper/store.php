<?php



namespace tool_log\helper;
defined('MOODLE_INTERNAL') || die();


trait store {

    
    protected $manager;

    
    protected $component;

    
    protected $store;


    
    protected function helper_setup(\tool_log\log\manager $manager) {
        $this->manager = $manager;
        $called = get_called_class();
        $parts = explode('\\', $called);
        if (!isset($parts[0]) || strpos($parts[0], 'logstore_') !== 0) {
            throw new \coding_exception("Store $called doesn't define classes in correct namespaces.");
        }
        $this->component = $parts[0];
        $this->store = str_replace('logstore_', '', $this->store);
    }

    
    protected function get_config($name, $default = null) {
        $value = get_config($this->component, $name);
        if ($value !== false) {
            return $value;
        }
        return $default;
    }

}
