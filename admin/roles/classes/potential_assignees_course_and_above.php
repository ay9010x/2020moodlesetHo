<?php



defined('MOODLE_INTERNAL') || die();


class core_role_potential_assignees_course_and_above extends core_role_assign_user_selector_base {
    public function find_users($search) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, '');

        $fields      = 'SELECT ' . $this->required_fields_sql('');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user}
                WHERE $wherecondition
                      AND id NOT IN (
                         SELECT r.userid
                           FROM {role_assignments} r
                          WHERE r.contextid = :contextid
                                AND r.roleid = :roleid)";

        list($sort, $sortparams) = users_order_by_sql('', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        $params['contextid'] = $this->context->id;
        $params['roleid'] = $this->roleid;

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
