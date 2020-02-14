<?php



defined('MOODLE_INTERNAL') || die();

class backup_logstore_standard_subplugin extends backup_tool_log_logstore_subplugin {

    
    protected function define_logstore_subplugin_structure() {

        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

                $otherelement = new base64_encode_final_element('other');

        $subpluginlog = new backup_nested_element('logstore_standard_log', array('id'), array(
            'eventname', 'component', 'action', 'target', 'objecttable',
            'objectid', 'crud', 'edulevel', 'contextid', 'userid', 'relateduserid',
            'anonymous', $otherelement, 'timecreated', 'ip', 'realuserid'));

        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginlog);

        $subpluginlog->set_source_table('logstore_standard_log', array('contextid' => backup::VAR_CONTEXTID));

        return $subplugin;
    }
}
