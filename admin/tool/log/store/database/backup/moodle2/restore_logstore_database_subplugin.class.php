<?php



defined('MOODLE_INTERNAL') || die();

class restore_logstore_database_subplugin extends restore_tool_log_logstore_subplugin {

    
    private static $extdb = null;

    
    private static $extdbtablename = null;

    
    public function __construct($subplugintype, $subpluginname, $step) {
                $enabledlogstores = explode(',', get_config('tool_log', 'enabled_stores'));
        if (in_array('logstore_database', $enabledlogstores)) {
            $manager = new \tool_log\log\manager();
            $store = new \logstore_database\log\store($manager);
            self::$extdb = $store->get_extdb();
            self::$extdbtablename = $store->get_config_value('dbtable');
        }

        parent::__construct($subplugintype, $subpluginname, $step);
    }

    
    protected function define_logstore_subplugin_structure() {
                $enabledlogstores = explode(',', get_config('tool_log', 'enabled_stores'));
        if (!in_array('logstore_database', $enabledlogstores)) {
            return array();         }

        $paths = array();

        $elename = $this->get_namefor('log');
        $elepath = $this->get_pathfor('/logstore_database_log');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    
    public function process_logstore_database_log($data) {
                if (!self::$extdb || !self::$extdbtablename) {
            return;
        }

        $data = $this->process_log($data);

        if ($data) {
            self::$extdb->insert_record(self::$extdbtablename, $data);
        }
    }
}
