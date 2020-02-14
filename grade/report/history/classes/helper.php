<?php



namespace gradereport_history;

defined('MOODLE_INTERNAL') || die;


class helper {

    
    public static function init_js($courseid, array $currentusers = null) {
        global $PAGE;

                $PAGE->requires->strings_for_js(array(
            'errajaxsearch',
            'finishselectingusers',
            'foundoneuser',
            'foundnusers',
            'loadmoreusers',
            'selectusers',
        ), 'gradereport_history');
        $PAGE->requires->strings_for_js(array(
            'loading'
        ), 'admin');
        $PAGE->requires->strings_for_js(array(
            'noresults',
            'search'
        ), 'moodle');

        $arguments = array(
            'courseid'            => $courseid,
            'ajaxurl'             => '/grade/report/history/users_ajax.php',
            'url'                 => $PAGE->url->out(false),
            'selectedUsers'       => $currentusers,
        );

                $PAGE->requires->yui_module(
            'moodle-gradereport_history-userselector',
            'Y.M.gradereport_history.UserSelector.init',
            array($arguments)
        );
    }

    
    public static function get_users($context, $search = '', $page = 0, $perpage = 25) {
        global $DB;

        list($sql, $params) = self::get_users_sql_and_params($context, $search);
        $limitfrom = $page * $perpage;
        $limitto = $limitfrom + $perpage;
        $users = $DB->get_records_sql($sql, $params, $limitfrom, $limitto);
        return $users;
    }

    
    public static function get_users_count($context, $search = '') {
        global $DB;

        list($sql, $params) = self::get_users_sql_and_params($context, $search, true);
        return $DB->count_records_sql($sql, $params);

    }

    
    protected static function get_users_sql_and_params($context, $search = '', $count = false) {

                $extrafields = get_extra_user_fields($context);
        $params = array();
        if (!empty($search)) {
            list($filtersql, $params) = users_search_sql($search, 'u', true, $extrafields);
            $filtersql .= ' AND ';
        } else {
            $filtersql = '';
        }

        $ufields = \user_picture::fields('u', $extrafields).',u.username';
        if ($count) {
            $select = "SELECT COUNT(DISTINCT u.id) ";
            $orderby = "";
        } else {
            $select = "SELECT DISTINCT $ufields ";
            $orderby = " ORDER BY u.lastname ASC, u.firstname ASC";
        }
        $sql = "$select
                 FROM {user} u
                 JOIN {grade_grades_history} ggh ON u.id = ggh.userid
                 JOIN {grade_items} gi ON gi.id = ggh.itemid
                WHERE $filtersql gi.courseid = :courseid";
        $sql .= $orderby;
        $params['courseid'] = $context->instanceid;

        return array($sql, $params);
    }

    
    public static function get_graders($courseid) {
        global $DB;

        $ufields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT u.id, $ufields
                  FROM {user} u
                  JOIN {grade_grades_history} ggh ON ggh.usermodified = u.id
                  JOIN {grade_items} gi ON gi.id = ggh.itemid
                 WHERE gi.courseid = :courseid
              GROUP BY u.id, $ufields
              ORDER BY u.lastname ASC, u.firstname ASC";

        $graders = $DB->get_records_sql($sql, array('courseid' => $courseid));
        $return = array(0 => get_string('allgraders', 'gradereport_history'));
        foreach ($graders as $grader) {
            $return[$grader->id] = fullname($grader);
        }
        return $return;
    }
}
