<?php



defined('MOODLE_INTERNAL') || die();


class core_role_existing_role_holders extends core_role_assign_user_selector_base {

    public function find_users($search) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, 'u');
        list($ctxcondition, $ctxparams) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'ctx');
        $params = array_merge($params, $ctxparams);
        $params['roleid'] = $this->roleid;

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $params = array_merge($params, $sortparams);

        $fields = "SELECT ra.id AS raid," . $this->required_fields_sql('u') . ",ra.contextid,ra.component ";
        $countfields = "SELECT COUNT(1) ";
        $sql = "FROM {role_assignments} ra
                  JOIN {user} u ON u.id = ra.userid
                  JOIN {context} ctx ON ra.contextid = ctx.id
                 WHERE $wherecondition
                       AND ctx.id $ctxcondition
                       AND ra.roleid = :roleid";
         $order = " ORDER BY ctx.depth DESC, ra.component, $sort";

        if (!$this->is_validating()) {
            $existinguserscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($existinguserscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $existinguserscount);
            }
        }

        $contextusers = $DB->get_records_sql($fields . $sql . $order, $params);

                if (empty($contextusers)) {
            return array();
        }

                                $dummyuser = new stdClass;
        $dummyuser->contextid = 0;
        $dummyuser->id = 0;
        $dummyuser->component = '';
        $contextusers[] = $dummyuser;
        $results = array();         $doneusers = array();         $currentcontextid = $this->context->id;
        $currentgroup = array();
        foreach ($contextusers as $user) {
            if (isset($doneusers[$user->id])) {
                continue;
            }
            $doneusers[$user->id] = 1;
            if ($user->contextid != $currentcontextid) {
                                if ($currentcontextid == $this->context->id) {
                    $groupname = $this->this_con_group_name($search, count($currentgroup));
                } else {
                    $groupname = $this->parent_con_group_name($search, $currentcontextid);
                }
                $results[$groupname] = $currentgroup;
                                $currentcontextid = $user->contextid;
                $currentgroup = array();
            }
                        unset($user->contextid);
            if ($currentcontextid != $this->context->id) {
                $user->disabled = true;
            }
            if ($user->component !== '') {
                                $user->disabled = true;
            }
            unset($user->component);
            $currentgroup[$user->id] = $user;
        }

        return $results;
    }

    protected function this_con_group_name($search, $numusers) {
        if ($this->context->contextlevel == CONTEXT_SYSTEM) {
                        if ($search) {
                return get_string('extusersmatching', 'core_role', $search);
            } else {
                return get_string('extusers', 'core_role');
            }
        }
        $contexttype = context_helper::get_level_name($this->context->contextlevel);
        if ($search) {
            $a = new stdClass;
            $a->search = $search;
            $a->contexttype = $contexttype;
            if ($numusers) {
                return get_string('usersinthisxmatching', 'core_role', $a);
            } else {
                return get_string('noneinthisxmatching', 'core_role', $a);
            }
        } else {
            if ($numusers) {
                return get_string('usersinthisx', 'core_role', $contexttype);
            } else {
                return get_string('noneinthisx', 'core_role', $contexttype);
            }
        }
    }

    protected function parent_con_group_name($search, $contextid) {
        $context = context::instance_by_id($contextid);
        $contextname = $context->get_context_name(true, true);
        if ($search) {
            $a = new stdClass;
            $a->contextname = $contextname;
            $a->search = $search;
            return get_string('usersfrommatching', 'core_role', $a);
        } else {
            return get_string('usersfrom', 'core_role', $contextname);
        }
    }
}
