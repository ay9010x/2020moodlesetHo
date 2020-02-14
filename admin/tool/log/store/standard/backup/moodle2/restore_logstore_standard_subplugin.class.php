<?php



defined('MOODLE_INTERNAL') || die();

class restore_logstore_standard_subplugin extends restore_tool_log_logstore_subplugin {

    
    protected function define_logstore_subplugin_structure() {

                $enabledlogstores = explode(',', get_config('tool_log', 'enabled_stores'));
        if (!in_array('logstore_standard', $enabledlogstores)) {
            return array();         }

        $paths = array();

        $elename = $this->get_namefor('log');
        $elepath = $this->get_pathfor('/logstore_standard_log');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    
    public function process_logstore_standard_log($data) {
        global $DB;

        $data = $this->process_log($data);

        if ($data) {
            $DB->insert_record('logstore_standard_log', $data);
        }
    }
}
