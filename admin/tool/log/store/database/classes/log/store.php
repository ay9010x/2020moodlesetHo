<?php



namespace logstore_database\log;
defined('MOODLE_INTERNAL') || die();

class store implements \tool_log\log\writer, \core\log\sql_reader {
    use \tool_log\helper\store,
        \tool_log\helper\reader,
        \tool_log\helper\buffered_writer {
        dispose as helper_dispose;
    }

    
    protected $extdb;

    
    protected $logguests;

    
    protected $includelevels = array();

    
    protected $includeactions = array();

    
    public function __construct(\tool_log\log\manager $manager) {
        $this->helper_setup($manager);
        $this->buffersize = $this->get_config('buffersize', 50);
        $this->logguests = $this->get_config('logguests', 1);
        $actions = $this->get_config('includeactions', '');
        $levels = $this->get_config('includelevels', '');
        $this->includeactions = $actions === '' ? array() : explode(',', $actions);
        $this->includelevels = $levels === '' ? array() : explode(',', $levels);
    }

    
    protected function init() {
        if (isset($this->extdb)) {
            return !empty($this->extdb);
        }

        $dbdriver = $this->get_config('dbdriver');
        if (empty($dbdriver)) {
            $this->extdb = false;
            return false;
        }
        list($dblibrary, $dbtype) = explode('/', $dbdriver);

        if (!$db = \moodle_database::get_driver_instance($dbtype, $dblibrary, true)) {
            debugging("Unknown driver $dblibrary/$dbtype", DEBUG_DEVELOPER);
            $this->extdb = false;
            return false;
        }

        $dboptions = array();
        $dboptions['dbpersist'] = $this->get_config('dbpersist', '0');
        $dboptions['dbsocket'] = $this->get_config('dbsocket', '');
        $dboptions['dbport'] = $this->get_config('dbport', '');
        $dboptions['dbschema'] = $this->get_config('dbschema', '');
        $dboptions['dbcollation'] = $this->get_config('dbcollation', '');
        try {
            $db->connect($this->get_config('dbhost'), $this->get_config('dbuser'), $this->get_config('dbpass'),
                $this->get_config('dbname'), false, $dboptions);
            $tables = $db->get_tables();
            if (!in_array($this->get_config('dbtable'), $tables)) {
                debugging('Cannot find the specified table', DEBUG_DEVELOPER);
                $this->extdb = false;
                return false;
            }
        } catch (\moodle_exception $e) {
            debugging('Cannot connect to external database: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $this->extdb = false;
            return false;
        }

        $this->extdb = $db;
        return true;
    }

    
    protected function is_event_ignored(\core\event\base $event) {
        if (!in_array($event->crud, $this->includeactions) &&
            !in_array($event->edulevel, $this->includelevels)
        ) {
                        return true;
        }
        if ((!CLI_SCRIPT or PHPUNIT_TEST) and !$this->logguests) {
                        if (!isloggedin() or isguestuser()) {
                return true;
            }
        }
        return false;
    }

    
    protected function insert_event_entries($evententries) {
        if (!$this->init()) {
            return;
        }
        if (!$dbtable = $this->get_config('dbtable')) {
            return;
        }
        try {
            $this->extdb->insert_records($dbtable, $evententries);
        } catch (\moodle_exception $e) {
            debugging('Cannot write to external database: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    
    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        if (!$this->init()) {
            return array();
        }

        if (!$dbtable = $this->get_config('dbtable')) {
            return array();
        }

        $sort = self::tweak_sort_by_id($sort);

        $events = array();
        $records = $this->extdb->get_records_select($dbtable, $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

        foreach ($records as $data) {
            if ($event = $this->get_log_event($data)) {
                $events[$data->id] = $event;
            }
        }

        return $events;
    }

    
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        if (!$this->init()) {
            return array();
        }

        if (!$dbtable = $this->get_config('dbtable')) {
            return array();
        }

        $sort = self::tweak_sort_by_id($sort);

        $recordset = $this->extdb->get_recordset_select($dbtable, $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

        return new \core\dml\recordset_walk($recordset, array($this, 'get_log_event'));
    }

    
    public function get_log_event($data) {

        $extra = array('origin' => $data->origin, 'ip' => $data->ip, 'realuserid' => $data->realuserid);
        $data = (array)$data;
        $id = $data['id'];
        $data['other'] = unserialize($data['other']);
        if ($data['other'] === false) {
            $data['other'] = array();
        }
        unset($data['origin']);
        unset($data['ip']);
        unset($data['realuserid']);
        unset($data['id']);

        if (!$event = \core\event\base::restore($data, $extra)) {
            return null;
        }

        return $event;
    }

    
    public function get_events_select_count($selectwhere, array $params) {
        if (!$this->init()) {
            return 0;
        }

        if (!$dbtable = $this->get_config('dbtable')) {
            return 0;
        }

        return $this->extdb->count_records_select($dbtable, $selectwhere, $params);
    }

    
    public function get_config_value($name, $default = null) {
        return $this->get_config($name, $default);
    }

    
    public function get_extdb() {
        if (!$this->init()) {
            return false;
        }

        return $this->extdb;
    }

    
    public function is_logging() {
        if (!$this->init()) {
            return false;
        }
        return true;
    }

    
    public function dispose() {
        $this->helper_dispose();
        if ($this->extdb) {
            $this->extdb->dispose();
        }
        $this->extdb = null;
    }
}
