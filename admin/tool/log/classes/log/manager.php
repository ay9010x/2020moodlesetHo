<?php



namespace tool_log\log;

defined('MOODLE_INTERNAL') || die();

class manager implements \core\log\manager {
    
    protected $readers;

    
    protected $writers;

    
    protected $stores;

    
    protected function init() {
        if (isset($this->stores)) {
                                    return;
        }
        $this->stores = array();
        $this->readers = array();
        $this->writers = array();

                \core_shutdown_manager::register_function(array($this, 'dispose'));

        $plugins = get_config('tool_log', 'enabled_stores');
        if (empty($plugins)) {
            return;
        }

        $plugins = explode(',', $plugins);
        foreach ($plugins as $plugin) {
            $classname = "\\$plugin\\log\\store";
            if (class_exists($classname)) {
                $store = new $classname($this);
                $this->stores[$plugin] = $store;
                if ($store instanceof \tool_log\log\writer) {
                    $this->writers[$plugin] = $store;
                }
                if ($store instanceof \core\log\reader) {
                    $this->readers[$plugin] = $store;
                }
            }
        }
    }

    
    public function process(\core\event\base $event) {
        $this->init();
        foreach ($this->writers as $plugin => $writer) {
            try {
                $writer->write($event, $this);
            } catch (\Exception $e) {
                debugging('Exception detected when logging event ' . $event->eventname . ' in ' . $plugin . ': ' .
                    $e->getMessage(), DEBUG_NORMAL, $e->getTrace());
            }
        }
    }

    
    public function get_readers($interface = null) {
        $this->init();
        $return = array();
        foreach ($this->readers as $plugin => $reader) {
            if (empty($interface) || ($reader instanceof $interface)) {
                $return[$plugin] = $reader;
            }
        }

        return $return;
    }

    
    public function get_supported_reports($logstore) {

        $allstores = self::get_store_plugins();
        if (empty($allstores[$logstore])) {
                        return array();
        }

        $reports = get_plugin_list_with_function('report', 'supports_logstore', 'lib.php');
        $enabled = $this->stores;

        if (empty($enabled[$logstore])) {
                        $classname = '\\' . $logstore . '\log\store';
            $instance = new $classname($this);
        } else {
            $instance = $enabled[$logstore];
        }

        $return = array();
        foreach ($reports as $report => $fulldir) {
            if (component_callback($report, 'supports_logstore', array($instance), false)) {
                $return[$report] = get_string('pluginname', $report);
            }
        }

        return $return;
    }

    
    public function get_supported_logstores($component) {

        $allstores = self::get_store_plugins();
        $enabled = $this->stores;

        $function = component_callback_exists($component, 'supports_logstore');
        if (!$function) {
                        return false;
        }

        $return = array();
        foreach ($allstores as $store => $logclass) {
            $instance = empty($enabled[$store]) ? new $logclass($this) : $enabled[$store];
            if ($function($instance)) {
                $return[$store] = get_string('pluginname', $store);
            }
        }
        return $return;
    }

    
    public static function get_store_plugins() {
        return \core_component::get_plugin_list_with_class('logstore', 'log\store');
    }

    
    public function dispose() {
        if ($this->stores) {
            foreach ($this->stores as $store) {
                $store->dispose();
            }
        }
        $this->stores = null;
        $this->readers = null;
        $this->writers = null;
    }

    
    public function legacy_add_to_log($courseid, $module, $action, $url = '', $info = '',
                                      $cm = 0, $user = 0, $ip = null, $time = null) {
        $this->init();
        if (isset($this->stores['logstore_legacy'])) {
            $this->stores['logstore_legacy']->legacy_add_to_log($courseid, $module, $action, $url, $info, $cm, $user, $ip, $time);
        }
    }
}
