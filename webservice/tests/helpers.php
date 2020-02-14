<?php



defined('MOODLE_INTERNAL') || die();


abstract class externallib_advanced_testcase extends advanced_testcase {

    
    public static function assignUserCapability($capability, $contextid, $roleid = null) {
        global $USER;

                if (empty($USER->id)) {
            $user  = self::getDataGenerator()->create_user();
            self::setUser($user);
        }

        if (empty($roleid)) {
            $roleid = create_role('Dummy role', 'dummyrole', 'dummy role description');
        }

        assign_capability($capability, CAP_ALLOW, $roleid, $contextid);

        role_assign($roleid, $USER->id, $contextid);

        accesslib_clear_all_caches_for_unit_testing();

        return $roleid;
    }

    
    public static function unassignUserCapability($capability, $contextid = null, $roleid = null, $courseid = null, $enrol = 'manual') {
        global $DB;

        if (!empty($courseid)) {
                        $instances = $DB->get_records('enrol', array('courseid'=>$courseid, 'enrol'=>$enrol));
            if (count($instances) != 1) {
                 throw new coding_exception('No found enrol instance for courseid: ' . $courseid . ' and enrol: ' . $enrol);
            }
            $instance = reset($instances);

            if (is_null($roleid) and $instance->roleid) {
                $roleid = $instance->roleid;
            }
        } else {
            if (empty($contextid) or empty($roleid)) {
                throw new coding_exception('unassignUserCapaibility requires contextid/roleid or courseid');
            }
        }

        unassign_capability($capability, $roleid, $contextid);

        accesslib_clear_all_caches_for_unit_testing();
    }
}

