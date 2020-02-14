<?php



defined('MOODLE_INTERNAL') || die;


class restore_lti_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $lti = new restore_path_element('lti', '/activity/lti');
        $paths[] = $lti;

                $this->add_subplugin_structure('ltisource', $lti);
        $this->add_subplugin_structure('ltiservice', $lti);

                return $this->prepare_activity_structure($paths);
    }

    protected function process_lti($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->servicesalt = uniqid('', true);

                 $data->grade = (int) $data->grade;

                                        $data->typeid = 0;

        $newitemid = $DB->insert_record('lti', $data);

                $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
                $this->add_related_files('mod_lti', 'intro', null);
    }
}
