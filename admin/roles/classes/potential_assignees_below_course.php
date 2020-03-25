<?php



defined('MOODLE_INTERNAL') || die();


class core_role_potential_assignees_below_course extends core_role_assign_user_selector_base {
    public function find_users($search) {
        global $DB;

        list($enrolsql, $eparams) = get_enrolled_sql($this->context);

                list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params = array_merge($params, $eparams);

        if ($wherecondition) {
            $wherecondition = ' AND ' . $wherecondition;
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(u.id)';

        $sql   = " FROM ($enrolsql) enrolled_users_view
                   JOIN {user} u ON u.id = enrolled_users_view.id
              LEFT JOIN {role_assignments} ra ON (ra.userid = enrolled_users_view.id AND
                                            ra.roleid = :roleid AND ra.contextid = :contextid)
                  WHERE ra.id IS NULL
                        $wherecondition";
        $params['contextid'] = $this->context->id;
        $params['roleid'] = $this->roleid;

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

                if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

                $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'core_role', $search);
        } else {
            $groupname = get_string('potusers', 'core_role');
        }

        return array($groupname => $availableusers);
    }
}
