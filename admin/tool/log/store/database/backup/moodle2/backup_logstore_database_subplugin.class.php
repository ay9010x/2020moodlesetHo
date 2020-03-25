<?php



defined('MOODLE_INTERNAL') || die();
require_once('backup_logstore_database_nested_element.php');

class backup_logstore_database_subplugin extends backup_tool_log_logstore_subplugin {

    
    protected function define_logstore_subplugin_structure() {
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

                $otherelement = new base64_encode_final_element('other');

        $subpluginlog = new backup_logstore_database_nested_element('logstore_database_log', array('id'), array(
            'eventname', 'component', 'action', 'target', 'objecttable',
            'objectid', 'crud', 'edulevel', 'contextid', 'userid', 'relateduserid',
            'anonymous', $otherelement, 'timecreated', 'ip', 'realuserid'));

        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginlog);

                $manager = new \tool_log\log\manager();
        $store = new \logstore_database\log\store($manager);
        $extdb = $store->get_extdb();

        if (!$extdb) {
            return false;
        }

        $subpluginlog->set_source_db($extdb);
        $subpluginlog->set_source_table($store->get_config_value('dbtable'), array('contextid' => backup::VAR_CONTEXTID));

        return $subplugin;
    }
}
