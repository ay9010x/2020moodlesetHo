<?php



defined('MOODLE_INTERNAL') || die();


class report_eventlist_list_generator {

    
    public static function get_all_events_list($detail = true) {
        return array_merge(self::get_core_events_list($detail), self::get_non_core_event_list($detail));
    }

    
    public static function get_core_events_list($detail = true) {
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
            $functionname = '\\core\\event\\' . $file;
                        if (method_exists($functionname, 'get_static_info')) {
                if ($detail) {
                    $ref = new \ReflectionClass($functionname);
                    if (!$ref->isAbstract() && $file != 'manager') {
                        $eventinformation = self::format_data($eventinformation, $functionname);
                    }
                } else {
                    $eventinformation[$functionname] = $file;
                }
            }
        }
                $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;
        return $eventinformation;
    }

    
    public static function get_crud_string($crudcharacter) {
        switch ($crudcharacter) {
            case 'c':
                return get_string('create', 'report_eventlist');
                break;

            case 'u':
                return get_string('update', 'report_eventlist');
                break;

            case 'd':
                return get_string('delete', 'report_eventlist');
                break;

            case 'r':
            default:
                return get_string('read', 'report_eventlist');
                break;
        }
    }

    
    public static function get_edulevel_string($edulevel) {
        switch ($edulevel) {
            case \core\event\base::LEVEL_PARTICIPATING:
                return get_string('participating', 'report_eventlist');
                break;

            case \core\event\base::LEVEL_TEACHING:
                return get_string('teaching', 'report_eventlist');
                break;

            case \core\event\base::LEVEL_OTHER:
            default:
                return get_string('other', 'report_eventlist');
                break;
        }
    }

    
    private static function get_file_list($directory) {
        global $CFG;
        $directoryroot = $CFG->dirroot;
        $finaleventfiles = array();
        if (is_dir($directory)) {
            if ($handle = opendir($directory)) {
                $eventfiles = scandir($directory);
                foreach ($eventfiles as $file) {
                    if ($file != '.' && $file != '..') {
                                                if (strrpos($directory, $directoryroot) !== false) {
                            $location = substr($directory, strlen($directoryroot));
                            $eventname = substr($file, 0, -4);
                            $finaleventfiles[$eventname] = $location  . '/' . $file;
                        }
                    }
                }
            }
        }
        return $finaleventfiles;
    }

    
    public static function get_non_core_event_list($detail = true) {
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
                    $plugineventname = '\\' . $plugintype . '_' . $plugin . '\\event\\' . $eventname;
                                        if (method_exists($plugineventname, 'get_static_info')) {
                        if ($detail) {
                            $ref = new \ReflectionClass($plugineventname);
                            if (!$ref->isAbstract() && $plugintype . '_' . $plugin !== 'logstore_legacy') {
                                $noncorepluginlist = self::format_data($noncorepluginlist, $plugineventname);
                            }
                        } else {
                            $noncorepluginlist[$plugineventname] = $eventname;
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

    
    public static function get_observer_list() {
        $events = \core\event\manager::get_all_observers();
        foreach ($events as $key => $observers) {
            foreach ($observers as $observerskey => $observer) {
                $events[$key][$observerskey]->parentplugin =
                        \core_plugin_manager::instance()->get_parent_of_subplugin($observer->plugintype);
            }
        }
        return $events;
    }

    
    private static function format_data($eventdata, $eventfullpath) {
                $eventdata[$eventfullpath] = $eventfullpath::get_static_info();
                $url = new \moodle_url('eventdetail.php', array('eventname' => $eventfullpath));
        $link = \html_writer::link($url, $eventfullpath::get_name());
        $eventdata[$eventfullpath]['fulleventname'] = \html_writer::span($link);
        $eventdata[$eventfullpath]['fulleventname'] .= \html_writer::empty_tag('br');
        $eventdata[$eventfullpath]['fulleventname'] .= \html_writer::span($eventdata[$eventfullpath]['eventname'],
                'report-eventlist-name');

        $eventdata[$eventfullpath]['crud'] = self::get_crud_string($eventdata[$eventfullpath]['crud']);
        $eventdata[$eventfullpath]['edulevel'] = self::get_edulevel_string($eventdata[$eventfullpath]['edulevel']);
        $eventdata[$eventfullpath]['legacyevent'] = $eventfullpath::get_legacy_eventname();

                $ref = new \ReflectionClass($eventdata[$eventfullpath]['eventname']);
        $eventdocbloc = $ref->getDocComment();
        $sincepattern = "/since\s*Moodle\s([0-9]+.[0-9]+)/i";
        preg_match($sincepattern, $eventdocbloc, $result);
        if (isset($result[1])) {
            $eventdata[$eventfullpath]['since'] = $result[1];
        } else {
            $eventdata[$eventfullpath]['since'] = null;
        }

                $pluginstring = explode('\\', $eventfullpath);
        if ($pluginstring[1] !== 'core') {
            $component = $eventdata[$eventfullpath]['component'];
            $manager = get_string_manager();
            if ($manager->string_exists('pluginname', $pluginstring[1])) {
                $eventdata[$eventfullpath]['component'] = \html_writer::span(get_string('pluginname', $pluginstring[1]));
            }
        }

                $eventdata[$eventfullpath]['raweventname'] = $eventfullpath::get_name() . ' ' . $eventdata[$eventfullpath]['eventname'];

                unset($eventdata[$eventfullpath]['action']);
        unset($eventdata[$eventfullpath]['target']);
        return $eventdata;
    }
}
