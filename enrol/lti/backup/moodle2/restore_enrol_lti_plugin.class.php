<?php



defined('MOODLE_INTERNAL') || die();


class restore_enrol_lti_plugin extends restore_enrol_plugin {

    
    protected $tools = array();

    
    protected function define_enrol_plugin_structure() {

        $paths = array();
        $paths[] = new restore_path_element('enrol_lti_tool', $this->connectionpoint->get_path() . '/tool');
        $paths[] = new restore_path_element('enrol_lti_users', $this->connectionpoint->get_path() . '/tool/users/user');

        return $paths;
    }

    
    public function process_enrol_lti_tool($data) {
        global $DB;

        $data = (object) $data;

                $oldid = $data->id;

                $data->timecreated = time();
        $data->timemodified = $data->timecreated;

                $data->id = $DB->insert_record('enrol_lti_tools', $data);

                $this->tools[$data->id] = $data;

                $this->set_mapping('enrol_lti_tool', $oldid, $data->id);
    }

    
    public function process_enrol_lti_users($data) {
        global $DB;

        $data = (object) $data;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->toolid = $this->get_mappingid('enrol_lti_tool', $data->toolid);
        $data->timecreated = time();

        $DB->insert_record('enrol_lti_users', $data);
    }

    
    public function after_restore_enrol() {
        global $DB;

                foreach ($this->tools as $tool) {
            $updatetool = new stdClass();
            $updatetool->id = $tool->id;
            $updatetool->enrolid = $this->get_mappingid('enrol', $tool->enrolid);
            $updatetool->contextid = $this->get_mappingid('context', $tool->contextid);
            $DB->update_record('enrol_lti_tools', $updatetool);
        }
    }
}
