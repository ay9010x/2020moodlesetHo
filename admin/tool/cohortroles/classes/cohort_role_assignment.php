<?php


namespace tool_cohortroles;

use lang_string;
use core_competency\persistent;


class cohort_role_assignment extends persistent {

    
    const TABLE = 'tool_cohortroles';

    
    protected static function define_properties() {
        return array(
            'userid' => array(
                'type' => PARAM_INT,
            ),
            'roleid' => array(
                'type' => PARAM_INT,
            ),
            'cohortid' => array(
                'type' => PARAM_INT,
            )
        );
    }

    
    protected function validate_userid($value) {
        global $DB;

        if (!$DB->record_exists('user', array('id' => $value))) {
            return new lang_string('invaliduserid', 'error');
        }

        return true;
    }

    
    protected function validate_roleid($value) {
        global $DB;

        if (!$DB->record_exists('role', array('id' => $value))) {
            return new lang_string('invalidroleid', 'error');
        }

        return true;
    }

    
    protected function validate_cohortid($value) {
        global $DB;

        if (!$DB->record_exists('cohort', array('id' => $value))) {
            return new lang_string('invalidcohortid', 'error');
        }

        return true;
    }

}
