<?php


namespace tool_cohortroles;

use stdClass;
use context_system;
use core_competency\invalid_persistent_exception;


class api {

    
    public static function create_cohort_role_assignment(stdClass $record) {
        $cohortroleassignment = new cohort_role_assignment(0, $record);
        $context = context_system::instance();

                require_capability('moodle/role:manage', $context);

                if (!$cohortroleassignment->is_valid()) {
            throw new invalid_persistent_exception($cohortroleassignment->get_errors());
        }

        $existing = cohort_role_assignment::get_record((array) $record);
        if (!empty($existing)) {
            return false;
        } else {
                        $cohortroleassignment->create();
        }
        return $cohortroleassignment;
    }

    
    public static function delete_cohort_role_assignment($id) {
        $cohortroleassignment = new cohort_role_assignment($id);
        $context = context_system::instance();

                require_capability('moodle/role:manage', $context);

                return $cohortroleassignment->delete();
    }

    
    public static function list_cohort_role_assignments($sort = '', $order = 'ASC', $skip = 0, $limit = 0) {
        $context = context_system::instance();

                require_capability('moodle/role:manage', $context);

                return cohort_role_assignment::get_records(array(), $sort, $order, $skip, $limit);
    }

    
    public static function count_cohort_role_assignments() {
        $context = context_system::instance();

                require_capability('moodle/role:manage', $context);

                return cohort_role_assignment::count_records();
    }

    
    public static function sync_all_cohort_roles() {
        global $DB;

        $context = context_system::instance();

                require_capability('moodle/role:manage', $context);

                $rolesadded = array();
        $rolesremoved = array();

                $all = cohort_role_assignment::get_records(array(), 'userid, roleid');
                $info = array();
        foreach ($all as $cra) {
            if (!isset($info[$cra->get_userid()])) {
                $info[$cra->get_userid()] = array();
            }
            if (!isset($info[$cra->get_userid()][$cra->get_roleid()])) {
                $info[$cra->get_userid()][$cra->get_roleid()] = array();
            }
            array_push($info[$cra->get_userid()][$cra->get_roleid()], $cra->get_cohortid());
        }
        
        foreach ($info as $userid => $roles) {
            foreach ($roles as $roleid => $cohorts) {
                list($cohortsql, $params) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED);

                $params['usercontext'] = CONTEXT_USER;
                $params['roleid'] = $roleid;
                $params['userid'] = $userid;

                $sql = 'SELECT u.id AS userid, ra.id, ctx.id AS contextid
                          FROM {user} u
                          JOIN {cohort_members} cm ON u.id = cm.userid
                          JOIN {context} ctx ON u.id = ctx.instanceid AND ctx.contextlevel = :usercontext
                          LEFT JOIN {role_assignments} ra ON ra.contextid = ctx.id
                           AND ra.roleid = :roleid
                           AND ra.userid = :userid
                         WHERE cm.cohortid ' . $cohortsql . '
                           AND ra.id IS NULL';

                $toadd = $DB->get_records_sql($sql, $params);

                foreach ($toadd as $add) {
                    role_assign($roleid, $userid, $add->contextid, 'tool_cohortroles');
                    $rolesadded[] = array(
                        'useridassignedto' => $userid,
                        'useridassignedover' => $add->userid,
                        'roleid' => $roleid
                    );
                }
            }
        }

                        foreach ($info as $userid => $roles) {
            foreach ($roles as $roleid => $cohorts) {
                                list($cohortsql, $params) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED);

                $params['usercontext'] = CONTEXT_USER;
                $params['roleid'] = $roleid;
                $params['userid'] = $userid;
                $params['component'] = 'tool_cohortroles';

                $sql = 'SELECT u.id as userid, ra.id, ctx.id AS contextid
                          FROM {user} u
                          JOIN {context} ctx ON u.id = ctx.instanceid AND ctx.contextlevel = :usercontext
                          JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.roleid = :roleid AND ra.userid = :userid
                     LEFT JOIN {cohort_members} cm ON u.id = cm.userid
                           AND cm.cohortid ' . $cohortsql . '
                         WHERE ra.component = :component AND cm.cohortid IS NULL';

                $toremove = $DB->get_records_sql($sql, $params);
                foreach ($toremove as $remove) {
                    role_unassign($roleid, $userid, $remove->contextid, 'tool_cohortroles');
                    $rolesremoved[] = array(
                        'useridassignedto' => $userid,
                        'useridassignedover' => $remove->userid,
                        'roleid' => $roleid
                    );
                }
            }
        }

        return array('rolesadded' => $rolesadded, 'rolesremoved' => $rolesremoved);
    }

}
