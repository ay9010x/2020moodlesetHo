<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class eventlist {
    
    protected static function get_core_eventlist() {
        global $CFG;

                        $debuglevel          = $CFG->debug;
        $debugdisplay        = $CFG->debugdisplay;
        $debugdeveloper      = $CFG->debugdeveloper;
        $CFG->debug          = 0;
        $CFG->debugdisplay   = false;
        $CFG->debugdeveloper = false;

        $eventinformation = array();
        $directory = $CFG->libdir . '/classes/event';
        $files = self::get_file_list($directory);

                if (isset($files['unknown_logged'])) {
            unset($files['unknown_logged']);
        }
        foreach ($files as $file => $location) {
            $classname = '\\core\\event\\' . $file;
                        if (method_exists($classname, 'get_static_info')) {
                $ref = new \ReflectionClass($classname);
                                if (!$ref->isAbstract() && $file != 'manager') {
                    $eventinformation[$classname] = $classname::get_name();
                }
            }
        }
                $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;
        return $eventinformation;
    }

    
    protected static function get_non_core_eventlist($withoutcomponent = false) {
        global $CFG;
                        $debuglevel          = $CFG->debug;
        $debugdisplay        = $CFG->debugdisplay;
        $debugdeveloper      = $CFG->debugdeveloper;
        $CFG->debug          = 0;
        $CFG->debugdisplay   = false;
        $CFG->debugdeveloper = false;

        $noncorepluginlist = array();
        $plugintypes = \core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $notused) {
            $pluginlist = \core_component::get_plugin_list($plugintype);
            foreach ($pluginlist as $plugin => $directory) {
                $plugindirectory = $directory . '/classes/event';
                foreach (self::get_file_list($plugindirectory) as $eventname => $notused) {
                    $fullpluginname = $plugintype . '_' . $plugin;
                    $plugineventname = '\\' . $fullpluginname . '\\event\\' . $eventname;
                                        if (method_exists($plugineventname, 'get_static_info')  && $fullpluginname !== 'tool_monitor') {                         $ref = new \ReflectionClass($plugineventname);
                        if (!$ref->isAbstract() && $fullpluginname !== 'logstore_legacy') {
                            if ($withoutcomponent) {
                                $noncorepluginlist[$plugineventname] = $plugineventname::get_name();
                            } else {
                                $noncorepluginlist[$fullpluginname][$plugineventname] = $plugineventname::get_name();
                            }
                        }
                    }
                }
            }
        }

                $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;

        return $noncorepluginlist;
    }

    
    protected static function get_file_list($directory) {
        global $CFG;
        $directoryroot = $CFG->dirroot;
        $finalfiles = array();
        if (is_dir($directory)) {
            if ($handle = opendir($directory)) {
                $files = scandir($directory);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                                                if (strrpos($directory, $directoryroot) !== false) {
                            $location = substr($directory, strlen($directoryroot));
                            $name = substr($file, 0, -4);
                            $finalfiles[$name] = $location  . '/' . $file;
                        }
                    }
                }
            }
        }
        return $finalfiles;
    }

    
    public static function get_all_eventlist($withoutcomponent = false) {
        if ($withoutcomponent) {
            $return = array_merge(self::get_core_eventlist(), self::get_non_core_eventlist($withoutcomponent));
            array_multisort($return, SORT_NATURAL);
        } else {
            $return = array_merge(array('core' => self::get_core_eventlist()),
                    self::get_non_core_eventlist($withoutcomponent = false));
        }
        return $return;
    }

    
    public static function get_plugin_list($eventlist = array()) {
        if (empty($eventlist)) {
            $eventlist = self::get_all_eventlist();
        }
        $plugins = array_keys($eventlist);
        $return = array();
        foreach ($plugins as $plugin) {
            if ($plugin === 'core') {
                $return[$plugin] = get_string('core', 'tool_monitor');
            } else if (get_string_manager()->string_exists('pluginname', $plugin)) {
                $return[$plugin] = get_string('pluginname', $plugin);
            } else {
                $return[$plugin] = $plugin;
            }
        }

        return $return;
    }

    
    public static function validate_event_plugin($plugin, $eventname, $eventlist = array()) {
        if (empty($eventlist)) {
            $eventlist = self::get_all_eventlist();
        }
        if (isset($eventlist[$plugin][$eventname])) {
            return true;
        }

        return false;
    }
}
