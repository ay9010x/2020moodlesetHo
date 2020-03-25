<?php

namespace core\event;

defined('MOODLE_INTERNAL') || die();




class manager {
    
    protected static $buffer = array();

    
    protected static $extbuffer = array();

    
    protected static $dispatching = false;

    
    protected static $allobservers = null;

    
    protected static $reloadaftertest = false;

    
    public static function dispatch(\core\event\base $event) {
        if (during_initial_install()) {
            return;
        }
        if (!$event->is_triggered() or $event->is_dispatched()) {
            throw new \coding_exception('Illegal event dispatching attempted.');
        }

        self::$buffer[] = $event;

        if (self::$dispatching) {
            return;
        }

        self::$dispatching = true;
        self::process_buffers();
        self::$dispatching = false;
    }

    
    public static function database_transaction_commited() {
        if (self::$dispatching or empty(self::$extbuffer)) {
            return;
        }

        self::$dispatching = true;
        self::process_buffers();
        self::$dispatching = false;
    }

    
    public static function database_transaction_rolledback() {
        self::$extbuffer = array();
    }

    protected static function process_buffers() {
        global $DB, $CFG;
        self::init_all_observers();

        while (self::$buffer or self::$extbuffer) {

            $fromextbuffer = false;
            $addedtoextbuffer = false;

            if (self::$extbuffer and !$DB->is_transaction_started()) {
                $fromextbuffer = true;
                $event = reset(self::$extbuffer);
                unset(self::$extbuffer[key(self::$extbuffer)]);

            } else if (self::$buffer) {
                $event = reset(self::$buffer);
                unset(self::$buffer[key(self::$buffer)]);

            } else {
                return;
            }

            $observingclasses = self::get_observing_classes($event);
            foreach ($observingclasses as $observingclass) {
                if (!isset(self::$allobservers[$observingclass])) {
                    continue;
                }
                foreach (self::$allobservers[$observingclass] as $observer) {
                    if ($observer->internal) {
                        if ($fromextbuffer) {
                                                                                    continue;
                        }
                    } else {
                        if ($DB->is_transaction_started()) {
                            if ($fromextbuffer) {
                                                                continue;
                            }
                                                        if (!$addedtoextbuffer) {
                                self::$extbuffer[] = $event;
                                $addedtoextbuffer = true;
                            }
                            continue;
                        }
                    }

                    if (isset($observer->includefile) and file_exists($observer->includefile)) {
                        include_once($observer->includefile);
                    }
                    if (is_callable($observer->callable)) {
                        try {
                            call_user_func($observer->callable, $event);
                        } catch (\Exception $e) {
                                                        if (empty($CFG->upgraderunning)) {
                                                                debugging("Exception encountered in event observer '$observer->callable': ".$e->getMessage(), DEBUG_DEVELOPER, $e->getTrace());
                            }
                        }
                    } else {
                        debugging("Can not execute event observer '$observer->callable'");
                    }
                }
            }

                    }
    }

    
    protected static function get_observing_classes(\core\event\base $event) {
        $classname = get_class($event);
        $observers = array('\\'.$classname);
        while ($classname = get_parent_class($classname)) {
            $observers[] = '\\'.$classname;
        }
        $observers = array_reverse($observers, false);

        return $observers;
    }

    
    protected static function init_all_observers() {
        global $CFG;

        if (is_array(self::$allobservers)) {
            return;
        }

        if (!PHPUNIT_TEST and !during_initial_install()) {
            $cache = \cache::make('core', 'observers');
            $cached = $cache->get('all');
            $dirroot = $cache->get('dirroot');
            if ($dirroot === $CFG->dirroot and is_array($cached)) {
                self::$allobservers = $cached;
                return;
            }
        }

        self::$allobservers = array();

        $plugintypes = \core_component::get_plugin_types();
        $plugintypes = array_merge(array('core' => 'not used'), $plugintypes);
        $systemdone = false;
        foreach ($plugintypes as $plugintype => $ignored) {
            if ($plugintype === 'core') {
                $plugins['core'] = "$CFG->dirroot/lib";
            } else {
                $plugins = \core_component::get_plugin_list($plugintype);
            }

            foreach ($plugins as $plugin => $fulldir) {
                if (!file_exists("$fulldir/db/events.php")) {
                    continue;
                }
                $observers = null;
                include("$fulldir/db/events.php");
                if (!is_array($observers)) {
                    continue;
                }
                self::add_observers($observers, "$fulldir/db/events.php", $plugintype, $plugin);
            }
        }

        self::order_all_observers();

        if (!PHPUNIT_TEST and !during_initial_install()) {
            $cache->set('all', self::$allobservers);
            $cache->set('dirroot', $CFG->dirroot);
        }
    }

    
    protected static function add_observers(array $observers, $file, $plugintype = null, $plugin = null) {
        global $CFG;

        foreach ($observers as $observer) {
            if (empty($observer['eventname']) or !is_string($observer['eventname'])) {
                debugging("Invalid 'eventname' detected in $file observer definition", DEBUG_DEVELOPER);
                continue;
            }
            if ($observer['eventname'] === '*') {
                $observer['eventname'] = '\core\event\base';
            }
            if (strpos($observer['eventname'], '\\') !== 0) {
                $observer['eventname'] = '\\'.$observer['eventname'];
            }
            if (empty($observer['callback'])) {
                debugging("Invalid 'callback' detected in $file observer definition", DEBUG_DEVELOPER);
                continue;
            }
            $o = new \stdClass();
            $o->callable = $observer['callback'];
            if (!isset($observer['priority'])) {
                $o->priority = 0;
            } else {
                $o->priority = (int)$observer['priority'];
            }
            if (!isset($observer['internal'])) {
                $o->internal = true;
            } else {
                $o->internal = (bool)$observer['internal'];
            }
            if (empty($observer['includefile'])) {
                $o->includefile = null;
            } else {
                if ($CFG->admin !== 'admin' and strpos($observer['includefile'], '/admin/') === 0) {
                    $observer['includefile'] = preg_replace('|^/admin/|', '/'.$CFG->admin.'/', $observer['includefile']);
                }
                $observer['includefile'] = $CFG->dirroot . '/' . ltrim($observer['includefile'], '/');
                if (!file_exists($observer['includefile'])) {
                    debugging("Invalid 'includefile' detected in $file observer definition", DEBUG_DEVELOPER);
                    continue;
                }
                $o->includefile = $observer['includefile'];
            }
            $o->plugintype = $plugintype;
            $o->plugin = $plugin;
            self::$allobservers[$observer['eventname']][] = $o;
        }
    }

    
    protected static function order_all_observers() {
        foreach (self::$allobservers as $classname => $observers) {
            \core_collator::asort_objects_by_property($observers, 'priority', \core_collator::SORT_NUMERIC);
            self::$allobservers[$classname] = array_reverse($observers);
        }
    }

    
    public static function get_all_observers() {
        self::init_all_observers();
        return self::$allobservers;
    }

    
    public static function phpunit_replace_observers(array $observers) {
        if (!PHPUNIT_TEST) {
            throw new \coding_exception('Cannot override event observers outside of phpunit tests!');
        }

        self::phpunit_reset();
        self::$allobservers = array();
        self::$reloadaftertest = true;

        self::add_observers($observers, 'phpunit');
        self::order_all_observers();

        return self::$allobservers;
    }

    
    public static function phpunit_reset() {
        if (!PHPUNIT_TEST) {
            throw new \coding_exception('Cannot reset event manager outside of phpunit tests!');
        }
        self::$buffer = array();
        self::$extbuffer = array();
        self::$dispatching = false;
        if (!self::$reloadaftertest) {
            self::$allobservers = null;
        }
        self::$reloadaftertest = false;
    }
}
