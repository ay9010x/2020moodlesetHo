<?php



namespace logstore_standard\log;

defined('MOODLE_INTERNAL') || die();

class store implements \tool_log\log\writer, \core\log\sql_internal_table_reader {
    use \tool_log\helper\store,
        \tool_log\helper\buffered_writer,
        \tool_log\helper\reader;

    
    protected $logguests;

    public function __construct(\tool_log\log\manager $manager) {
        $this->helper_setup($manager);
                $this->logguests = $this->get_config('logguests', 1);
    }

    
    protected function is_event_ignored(\core\event\base $event) {
        if ((!CLI_SCRIPT or PHPUNIT_TEST) and !$this->logguests) {
                        if (!isloggedin() or isguestuser()) {
                return true;
            }
        }
        return false;
    }

    
    protected function insert_event_entries($evententries) {
        global $DB;

        $DB->insert_records('logstore_standard_log', $evententries);
    }

    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

        $events = array();
        $records = $DB->get_recordset_select('logstore_standard_log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

        foreach ($records as $data) {
            if ($event = $this->get_log_event($data)) {
                $events[$data->id] = $event;
            }
        }

        $records->close();

        return $events;
    }

    
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

        $recordset = $DB->get_recordset_select('logstore_standard_log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

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
        global $DB;
        return $DB->count_records_select('logstore_standard_log', $selectwhere, $params);
    }

    public function get_internal_log_table_name() {
        return 'logstore_standard_log';
    }

    
    public function is_logging() {
                        return true;
    }
}
