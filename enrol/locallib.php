<?php



defined('MOODLE_INTERNAL') || die();


class course_enrolment_manager {

    
    protected $context;
    
    protected $course = null;
    
    protected $instancefilter = null;
    
    protected $rolefilter = 0;
    
    protected $searchfilter = '';
    
    protected $groupfilter = 0;
    
    protected $statusfilter = -1;

    
    protected $totalusers = null;
    
    protected $users = array();

    
    protected $otherusers = array();

    
    protected $totalotherusers = null;

    
    protected $moodlepage = null;

    
    private $_instancessql = null;
    private $_instances = null;
    private $_inames = null;
    private $_plugins = null;
    private $_allplugins = null;
    private $_roles = null;
    private $_assignableroles = null;
    private $_assignablerolesothers = null;
    private $_groups = null;
    

    
    public function __construct(moodle_page $moodlepage, $course, $instancefilter = null,
            $rolefilter = 0, $searchfilter = '', $groupfilter = 0, $statusfilter = -1) {
        $this->moodlepage = $moodlepage;
        $this->context = context_course::instance($course->id);
        $this->course = $course;
        $this->instancefilter = $instancefilter;
        $this->rolefilter = $rolefilter;
        $this->searchfilter = $searchfilter;
        $this->groupfilter = $groupfilter;
        $this->statusfilter = $statusfilter;
    }

    
    public function get_moodlepage() {
        return $this->moodlepage;
    }

    
    public function get_total_users() {
        global $DB;
        if ($this->totalusers === null) {
            list($instancessql, $params, $filter) = $this->get_instance_sql();
            list($filtersql, $moreparams) = $this->get_filter_sql();
            $params += $moreparams;
            $sqltotal = "SELECT COUNT(DISTINCT u.id)
                           FROM {user} u
                           JOIN {user_enrolments} ue ON (ue.userid = u.id  AND ue.enrolid $instancessql)
                           JOIN {enrol} e ON (e.id = ue.enrolid)";
            if ($this->groupfilter) {
                $sqltotal .= " LEFT JOIN ({groups_members} gm JOIN {groups} g ON (g.id = gm.groupid))
                                         ON (u.id = gm.userid AND g.courseid = e.courseid)";
            }
            $sqltotal .= "WHERE $filtersql";
            $this->totalusers = (int)$DB->count_records_sql($sqltotal, $params);
        }
        return $this->totalusers;
    }

    
    public function get_total_other_users() {
        global $DB;
        if ($this->totalotherusers === null) {
            list($ctxcondition, $params) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'ctx');
            $params['courseid'] = $this->course->id;
            $sql = "SELECT COUNT(DISTINCT u.id)
                      FROM {role_assignments} ra
                      JOIN {user} u ON u.id = ra.userid
                      JOIN {context} ctx ON ra.contextid = ctx.id
                 LEFT JOIN (
                           SELECT ue.id, ue.userid
                             FROM {user_enrolments} ue
                        LEFT JOIN {enrol} e ON e.id=ue.enrolid
                            WHERE e.courseid = :courseid
                         ) ue ON ue.userid=u.id
                     WHERE ctx.id $ctxcondition AND
                           ue.id IS NULL";
            $this->totalotherusers = (int)$DB->count_records_sql($sql, $params);
        }
        return $this->totalotherusers;
    }

    
    public function get_users($sort, $direction='ASC', $page=0, $perpage=25) {
        global $DB;
        if ($direction !== 'ASC') {
            $direction = 'DESC';
        }
        $key = md5("$sort-$direction-$page-$perpage");
        if (!array_key_exists($key, $this->users)) {
            list($instancessql, $params, $filter) = $this->get_instance_sql();
            list($filtersql, $moreparams) = $this->get_filter_sql();
            $params += $moreparams;
            $extrafields = get_extra_user_fields($this->get_context());
            $extrafields[] = 'lastaccess';
            $ufields = user_picture::fields('u', $extrafields);
            $sql = "SELECT DISTINCT $ufields, COALESCE(ul.timeaccess, 0) AS lastcourseaccess
                      FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id  AND ue.enrolid $instancessql)
                      JOIN {enrol} e ON (e.id = ue.enrolid)
                 LEFT JOIN {user_lastaccess} ul ON (ul.courseid = e.courseid AND ul.userid = u.id)";
            if ($this->groupfilter) {
                $sql .= " LEFT JOIN ({groups_members} gm JOIN {groups} g ON (g.id = gm.groupid))
                                    ON (u.id = gm.userid AND g.courseid = e.courseid)";
            }
            $sql .= "WHERE $filtersql
                  ORDER BY $sort $direction";
            $this->users[$key] = $DB->get_records_sql($sql, $params, $page*$perpage, $perpage);
        }
        return $this->users[$key];
    }

    
    protected function get_filter_sql() {
        global $DB;

                $extrafields = get_extra_user_fields($this->get_context());
        list($sql, $params) = users_search_sql($this->searchfilter, 'u', true, $extrafields);

                if ($this->rolefilter) {
                        $contextids = $this->context->get_parent_context_ids();
            $contextids[] = $this->context->id;
            list($contextsql, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
            $params += $contextparams;

                        $sql .= " AND (SELECT COUNT(1) FROM {role_assignments} ra WHERE ra.userid = u.id " .
                    "AND ra.roleid = :roleid AND ra.contextid $contextsql) > 0";
            $params['roleid'] = $this->rolefilter;
        }

                if ($this->groupfilter) {
            if ($this->groupfilter < 0) {
                                $sql .= " AND gm.groupid IS NULL";
            } else {
                $sql .= " AND gm.groupid = :groupid";
                $params['groupid'] = $this->groupfilter;
            }
        }

                if ($this->statusfilter === ENROL_USER_ACTIVE) {
            $sql .= " AND ue.status = :active AND e.status = :enabled AND ue.timestart < :now1
                    AND (ue.timeend = 0 OR ue.timeend > :now2)";
            $now = round(time(), -2);             $params += array('enabled' => ENROL_INSTANCE_ENABLED,
                             'active' => ENROL_USER_ACTIVE,
                             'now1' => $now,
                             'now2' => $now);
        } else if ($this->statusfilter === ENROL_USER_SUSPENDED) {
            $sql .= " AND (ue.status = :inactive OR e.status = :disabled OR ue.timestart > :now1
                    OR (ue.timeend <> 0 AND ue.timeend < :now2))";
            $now = round(time(), -2);             $params += array('disabled' => ENROL_INSTANCE_DISABLED,
                             'inactive' => ENROL_USER_SUSPENDED,
                             'now1' => $now,
                             'now2' => $now);
        }

        return array($sql, $params);
    }

    
    public function get_other_users($sort, $direction='ASC', $page=0, $perpage=25) {
        global $DB;
        if ($direction !== 'ASC') {
            $direction = 'DESC';
        }
        $key = md5("$sort-$direction-$page-$perpage");
        if (!array_key_exists($key, $this->otherusers)) {
            list($ctxcondition, $params) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'ctx');
            $params['courseid'] = $this->course->id;
            $params['cid'] = $this->course->id;
            $extrafields = get_extra_user_fields($this->get_context());
            $ufields = user_picture::fields('u', $extrafields);
            $sql = "SELECT ra.id as raid, ra.contextid, ra.component, ctx.contextlevel, ra.roleid, $ufields,
                        coalesce(u.lastaccess,0) AS lastaccess
                    FROM {role_assignments} ra
                    JOIN {user} u ON u.id = ra.userid
                    JOIN {context} ctx ON ra.contextid = ctx.id
               LEFT JOIN (
                       SELECT ue.id, ue.userid
                         FROM {user_enrolments} ue
                         JOIN {enrol} e ON e.id = ue.enrolid
                        WHERE e.courseid = :courseid
                       ) ue ON ue.userid=u.id
                   WHERE ctx.id $ctxcondition AND
                         ue.id IS NULL
                ORDER BY $sort $direction, ctx.depth DESC";
            $this->otherusers[$key] = $DB->get_records_sql($sql, $params, $page*$perpage, $perpage);
        }
        return $this->otherusers[$key];
    }

    
    protected function get_basic_search_conditions($search, $searchanywhere) {
        global $DB, $CFG;

                $tests = array("u.id <> :guestid", 'u.deleted = 0', 'u.confirmed = 1');
        $params = array('guestid' => $CFG->siteguest);
        if (!empty($search)) {
            $conditions = get_extra_user_fields($this->get_context());
            $conditions[] = 'u.firstname';
            $conditions[] = 'u.lastname';
            $conditions[] = $DB->sql_fullname('u.firstname', 'u.lastname');
            if ($searchanywhere) {
                $searchparam = '%' . $search . '%';
            } else {
                $searchparam = $search . '%';
            }
            $i = 0;
            foreach ($conditions as $key => $condition) {
                $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false);
                $params["con{$i}00"] = $searchparam;
                $i++;
            }
            $tests[] = '(' . implode(' OR ', $conditions) . ')';
        }
        $wherecondition = implode(' AND ', $tests);

        $extrafields = get_extra_user_fields($this->get_context(), array('username', 'lastaccess'));
        $extrafields[] = 'username';
        $extrafields[] = 'lastaccess';
        $ufields = user_picture::fields('u', $extrafields);

        return array($ufields, $params, $wherecondition);
    }

    
    protected function execute_search_queries($search, $fields, $countfields, $sql, array $params, $page, $perpage, $addedenrollment=0) {
        global $DB, $CFG;

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->get_context());
        $order = ' ORDER BY ' . $sort;

        $totalusers = $DB->count_records_sql($countfields . $sql, $params);
        $availableusers = $DB->get_records_sql($fields . $sql . $order,
                array_merge($params, $sortparams), ($page*$perpage) - $addedenrollment, $perpage);

        return array('totalusers' => $totalusers, 'users' => $availableusers);
    }

    
    public function get_potential_users($enrolid, $search='', $searchanywhere=false, $page=0, $perpage=25, $addedenrollment=0) {
        global $DB;

        list($ufields, $params, $wherecondition) = $this->get_basic_search_conditions($search, $searchanywhere);

        $fields      = 'SELECT '.$ufields;
        $countfields = 'SELECT COUNT(1)';
        $sql = " FROM {user} u
            LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition
                      AND ue.id IS NULL";
        $params['enrolid'] = $enrolid;

        return $this->execute_search_queries($search, $fields, $countfields, $sql, $params, $page, $perpage, $addedenrollment);
    }

    
    public function search_other_users($search='', $searchanywhere=false, $page=0, $perpage=25) {
        global $DB, $CFG;

        list($ufields, $params, $wherecondition) = $this->get_basic_search_conditions($search, $searchanywhere);

        $fields      = 'SELECT ' . $ufields;
        $countfields = 'SELECT COUNT(u.id)';
        $sql   = " FROM {user} u
              LEFT JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.contextid = :contextid)
                  WHERE $wherecondition
                    AND ra.id IS NULL";
        $params['contextid'] = $this->context->id;

        return $this->execute_search_queries($search, $fields, $countfields, $sql, $params, $page, $perpage);
    }

    
    protected function get_instance_sql() {
        global $DB;
        if ($this->_instancessql === null) {
            $instances = $this->get_enrolment_instances();
            $filter = $this->get_enrolment_filter();
            if ($filter && array_key_exists($filter, $instances)) {
                $sql = " = :ifilter";
                $params = array('ifilter'=>$filter);
            } else {
                $filter = 0;
                if ($instances) {
                    list($sql, $params) = $DB->get_in_or_equal(array_keys($this->get_enrolment_instances()), SQL_PARAMS_NAMED);
                } else {
                                        $sql = "= :never";
                    $params = array('never'=>-1);
                }
            }
            $this->instancefilter = $filter;
            $this->_instancessql = array($sql, $params, $filter);
        }
        return $this->_instancessql;
    }

    
    public function get_enrolment_instances() {
        if ($this->_instances === null) {
            $this->_instances = enrol_get_instances($this->course->id, false);
        }
        return $this->_instances;
    }

    
    public function get_enrolment_instance_names() {
        if ($this->_inames === null) {
            $instances = $this->get_enrolment_instances();
            $plugins = $this->get_enrolment_plugins(false);
            foreach ($instances as $key=>$instance) {
                if (!isset($plugins[$instance->enrol])) {
                                        unset($instances[$key]);
                    continue;
                }
                $this->_inames[$key] = $plugins[$instance->enrol]->get_instance_name($instance);
            }
        }
        return $this->_inames;
    }

    
    public function get_enrolment_plugins($onlyenabled = true) {
        if ($this->_plugins === null) {
            $this->_plugins = enrol_get_plugins(true);
        }

        if ($onlyenabled) {
            return $this->_plugins;
        }

        if ($this->_allplugins === null) {
                        $this->_allplugins = $this->_plugins;
            foreach (enrol_get_plugins(false) as $name=>$plugin) {
                if (!isset($this->_allplugins[$name])) {
                    $this->_allplugins[$name] = $plugin;
                }
            }
        }

        return $this->_allplugins;
    }

    
    public function get_all_roles() {
        if ($this->_roles === null) {
            $this->_roles = role_fix_names(get_all_roles($this->context), $this->context);
        }
        return $this->_roles;
    }

    
    public function get_assignable_roles($otherusers = false) {
        if ($this->_assignableroles === null) {
            $this->_assignableroles = get_assignable_roles($this->context, ROLENAME_ALIAS, false);         }

        if ($otherusers) {
            if (!is_array($this->_assignablerolesothers)) {
                $this->_assignablerolesothers = array();
                list($courseviewroles, $ignored) = get_roles_with_cap_in_context($this->context, 'moodle/course:view');
                foreach ($this->_assignableroles as $roleid=>$role) {
                    if (isset($courseviewroles[$roleid])) {
                        $this->_assignablerolesothers[$roleid] = $role;
                    }
                }
            }
            return $this->_assignablerolesothers;
        } else {
            return $this->_assignableroles;
        }
    }

    
    public function get_assignable_roles_for_json($otherusers = false, $manual = false) {
        $rolesarray = array();
        $assignable = $this->get_assignable_roles($otherusers);
        foreach ($assignable as $id => $role) {
            if($manual){
                if($role->shortname == 'teacher'){
                    $rolesarray[] = array('id' => $id, 'name' => $role);
                }
            }
            else{
                $rolesarray[] = array('id' => $id, 'name' => $role);
            }
        }
        return $rolesarray;
    }
    
    public function get_manual_assignable_roles_for_json($otherusers = false) {
        $rolesarray = array();
        $assignable = $this->get_assignable_roles($otherusers);
        foreach ($assignable as $id => $role) {
            if($role->shortname == 'teacher'){
                $rolesarray[] = array('id' => $id, 'name' => $role);
            }
        }
        return $rolesarray;
    }

    
    public function get_all_groups() {
        if ($this->_groups === null) {
            $this->_groups = groups_get_all_groups($this->course->id);
            foreach ($this->_groups as $gid=>$group) {
                $this->_groups[$gid]->name = format_string($group->name);
            }
        }
        return $this->_groups;
    }

    
    public function unenrol_user($ue) {
        global $DB;
        list ($instance, $plugin) = $this->get_user_enrolment_components($ue);
        if ($instance && $plugin && $plugin->allow_unenrol_user($instance, $ue) && has_capability("enrol/$instance->enrol:unenrol", $this->context)) {
            $plugin->unenrol_user($instance, $ue->userid);
            return true;
        }
        return false;
    }

    
    public function get_user_enrolment_components($userenrolment) {
        global $DB;
        if (is_numeric($userenrolment)) {
            $userenrolment = $DB->get_record('user_enrolments', array('id'=>(int)$userenrolment));
        }
        $instances = $this->get_enrolment_instances();
        $plugins = $this->get_enrolment_plugins(false);
        if (!$userenrolment || !isset($instances[$userenrolment->enrolid])) {
            return array(false, false);
        }
        $instance = $instances[$userenrolment->enrolid];
        $plugin = $plugins[$instance->enrol];
        return array($instance, $plugin);
    }

    
    public function unassign_role_from_user($userid, $roleid) {
        global $DB;
                if (!is_siteadmin() and !array_key_exists($roleid, $this->get_assignable_roles())) {
            if (defined('AJAX_SCRIPT')) {
                throw new moodle_exception('invalidrole');
            }
            return false;
        }
        $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
        $ras = $DB->get_records('role_assignments', array('contextid'=>$this->context->id, 'userid'=>$user->id, 'roleid'=>$roleid));
        foreach ($ras as $ra) {
            if ($ra->component) {
                if (strpos($ra->component, 'enrol_') !== 0) {
                    continue;
                }
                if (!$plugin = enrol_get_plugin(substr($ra->component, 6))) {
                    continue;
                }
                if ($plugin->roles_protected()) {
                    continue;
                }
            }
            role_unassign($ra->roleid, $ra->userid, $ra->contextid, $ra->component, $ra->itemid);
        }
        return true;
    }

    
    public function assign_role_to_user($roleid, $userid) {
        require_capability('moodle/role:assign', $this->context);
        if (!array_key_exists($roleid, $this->get_assignable_roles())) {
            if (defined('AJAX_SCRIPT')) {
                throw new moodle_exception('invalidrole');
            }
            return false;
        }
        return role_assign($roleid, $userid, $this->context->id, '', NULL);
    }

    
    public function add_user_to_group($user, $groupid) {
        require_capability('moodle/course:managegroups', $this->context);
        $group = $this->get_group($groupid);
        if (!$group) {
            return false;
        }
        return groups_add_member($group->id, $user->id);
    }

    
    public function remove_user_from_group($user, $groupid) {
        global $DB;
        require_capability('moodle/course:managegroups', $this->context);
        $group = $this->get_group($groupid);
        if (!groups_remove_member_allowed($group, $user)) {
            return false;
        }
        if (!$group) {
            return false;
        }
        return groups_remove_member($group, $user);
    }

    
    public function get_group($groupid) {
        $groups = $this->get_all_groups();
        if (!array_key_exists($groupid, $groups)) {
            return false;
        }
        return $groups[$groupid];
    }

    
    public function edit_enrolment($userenrolment, $data) {
                        list($instance, $plugin) = $this->get_user_enrolment_components($userenrolment);
        if ($instance && $plugin && $plugin->allow_manage($instance) && has_capability("enrol/$instance->enrol:manage", $this->context)) {
            if (!isset($data->status)) {
                $data->status = $userenrolment->status;
            }
            $plugin->update_user_enrol($instance, $userenrolment->userid, $data->status, $data->timestart, $data->timeend);
            return true;
        }
        return false;
    }

    
    public function get_enrolment_filter() {
        return $this->instancefilter;
    }

    
    public function get_user_roles($userid) {
        $roles = array();
        $ras = get_user_roles($this->context, $userid, true, 'c.contextlevel DESC, r.sortorder ASC');
        $plugins = $this->get_enrolment_plugins(false);
        foreach ($ras as $ra) {
            if ($ra->contextid != $this->context->id) {
                if (!array_key_exists($ra->roleid, $roles)) {
                    $roles[$ra->roleid] = null;
                }
                                continue;
            }
            if (array_key_exists($ra->roleid, $roles) && $roles[$ra->roleid] === false) {
                continue;
            }
            $changeable = true;
            if ($ra->component) {
                $changeable = false;
                if (strpos($ra->component, 'enrol_') === 0) {
                    $plugin = substr($ra->component, 6);
                    if (isset($plugins[$plugin])) {
                        $changeable = !$plugins[$plugin]->roles_protected();
                    }
                }
            }

            $roles[$ra->roleid] = $changeable;
        }
        return $roles;
    }

    
    public function get_user_enrolments($userid) {
        global $DB;
        list($instancessql, $params, $filter) = $this->get_instance_sql();
        $params['userid'] = $userid;
        $userenrolments = $DB->get_records_select('user_enrolments', "enrolid $instancessql AND userid = :userid", $params);
        $instances = $this->get_enrolment_instances();
        $plugins = $this->get_enrolment_plugins(false);
        $inames = $this->get_enrolment_instance_names();
        foreach ($userenrolments as &$ue) {
            $ue->enrolmentinstance     = $instances[$ue->enrolid];
            $ue->enrolmentplugin       = $plugins[$ue->enrolmentinstance->enrol];
            $ue->enrolmentinstancename = $inames[$ue->enrolmentinstance->id];
        }
        return $userenrolments;
    }

    
    public function get_user_groups($userid) {
        return groups_get_all_groups($this->course->id, $userid, 0, 'g.id');
    }

    
    public function get_url_params() {
        $args = array(
            'id' => $this->course->id
        );
        if (!empty($this->instancefilter)) {
            $args['ifilter'] = $this->instancefilter;
        }
        if (!empty($this->rolefilter)) {
            $args['role'] = $this->rolefilter;
        }
        if ($this->searchfilter !== '') {
            $args['search'] = $this->searchfilter;
        }
        if (!empty($this->groupfilter)) {
            $args['filtergroup'] = $this->groupfilter;
        }
        if ($this->statusfilter !== -1) {
            $args['status'] = $this->statusfilter;
        }
        return $args;
    }

    
    public function get_course() {
        return $this->course;
    }

    
    public function get_context() {
        return $this->context;
    }

    
    public function get_other_users_for_display(core_enrol_renderer $renderer, moodle_url $pageurl, $sort, $direction, $page, $perpage) {

        $userroles = $this->get_other_users($sort, $direction, $page, $perpage);
        $roles = $this->get_all_roles();
        $plugins = $this->get_enrolment_plugins(false);

        $context    = $this->get_context();
        $now = time();
        $extrafields = get_extra_user_fields($context);

        $users = array();
        foreach ($userroles as $userrole) {
            $contextid = $userrole->contextid;
            unset($userrole->contextid);             if (!array_key_exists($userrole->id, $users)) {
                $users[$userrole->id] = $this->prepare_user_for_display($userrole, $extrafields, $now);
            }
            $a = new stdClass;
            $a->role = $roles[$userrole->roleid]->localname;
            if ($contextid == $this->context->id) {
                $changeable = true;
                if ($userrole->component) {
                    $changeable = false;
                    if (strpos($userrole->component, 'enrol_') === 0) {
                        $plugin = substr($userrole->component, 6);
                        if (isset($plugins[$plugin])) {
                            $changeable = !$plugins[$plugin]->roles_protected();
                        }
                    }
                }
                $roletext = get_string('rolefromthiscourse', 'enrol', $a);
            } else {
                $changeable = false;
                switch ($userrole->contextlevel) {
                    case CONTEXT_COURSE :
                                                $roletext = get_string('rolefrommetacourse', 'enrol', $a);
                        break;
                    case CONTEXT_COURSECAT :
                        $roletext = get_string('rolefromcategory', 'enrol', $a);
                        break;
                    case CONTEXT_SYSTEM:
                    default:
                        $roletext = get_string('rolefromsystem', 'enrol', $a);
                        break;
                }
            }
            if (!isset($users[$userrole->id]['roles'])) {
                $users[$userrole->id]['roles'] = array();
            }
            $users[$userrole->id]['roles'][$userrole->roleid] = array(
                'text' => $roletext,
                'unchangeable' => !$changeable
            );
        }
        return $users;
    }

    
    public function get_users_for_display(course_enrolment_manager $manager, $sort, $direction, $page, $perpage) {
        $pageurl = $manager->get_moodlepage()->url;
        $users = $this->get_users($sort, $direction, $page, $perpage);

        $now = time();
        $straddgroup = get_string('addgroup', 'group');
        $strunenrol = get_string('unenrol', 'enrol');
        $stredit = get_string('edit');

        $allroles   = $this->get_all_roles();
        $assignable = $this->get_assignable_roles();
        $allgroups  = $this->get_all_groups();
        $context    = $this->get_context();
        $canmanagegroups = has_capability('moodle/course:managegroups', $context);

        $url = new moodle_url($pageurl, $this->get_url_params());
        $extrafields = get_extra_user_fields($context);

        $enabledplugins = $this->get_enrolment_plugins(true);

        $userdetails = array();
        foreach ($users as $user) {
            $details = $this->prepare_user_for_display($user, $extrafields, $now);

                        $details['roles'] = array();
            foreach ($this->get_user_roles($user->id) as $rid=>$rassignable) {
                $unchangeable = !$rassignable;
                if (!is_siteadmin() and !isset($assignable[$rid])) {
                    $unchangeable = true;
                }
                $details['roles'][$rid] = array('text'=>$allroles[$rid]->localname, 'unchangeable'=>$unchangeable);
            }

                        $usergroups = $this->get_user_groups($user->id);
            $details['groups'] = array();
            foreach($usergroups as $gid=>$unused) {
                $details['groups'][$gid] = $allgroups[$gid]->name;
            }

                        $details['enrolments'] = array();
            foreach ($this->get_user_enrolments($user->id) as $ue) {
                if (!isset($enabledplugins[$ue->enrolmentinstance->enrol])) {
                    $details['enrolments'][$ue->id] = array(
                        'text' => $ue->enrolmentinstancename,
                        'period' => null,
                        'dimmed' =>  true,
                        'actions' => array()
                    );
                    continue;
                } else if ($ue->timestart and $ue->timeend) {
                    $period = get_string('periodstartend', 'enrol', array('start'=>userdate($ue->timestart), 'end'=>userdate($ue->timeend)));
                    $periodoutside = ($ue->timestart && $ue->timeend && ($now < $ue->timestart || $now > $ue->timeend));
                } else if ($ue->timestart) {
                    $period = get_string('periodstart', 'enrol', userdate($ue->timestart));
                    $periodoutside = ($ue->timestart && $now < $ue->timestart);
                } else if ($ue->timeend) {
                    $period = get_string('periodend', 'enrol', userdate($ue->timeend));
                    $periodoutside = ($ue->timeend && $now > $ue->timeend);
                } else {
                                        $period = get_string('periodnone', 'enrol', userdate($ue->timecreated));
                    $periodoutside = false;
                }
                $details['enrolments'][$ue->id] = array(
                    'text' => $ue->enrolmentinstancename,
                    'period' => $period,
                    'dimmed' =>  ($periodoutside or $ue->status != ENROL_USER_ACTIVE or $ue->enrolmentinstance->status != ENROL_INSTANCE_ENABLED),
                    'actions' => $ue->enrolmentplugin->get_user_enrolment_actions($manager, $ue)
                );
            }
            $userdetails[$user->id] = $details;
        }
        return $userdetails;
    }

    
    private function prepare_user_for_display($user, $extrafields, $now) {
        $details = array(
            'userid'              => $user->id,
            'courseid'            => $this->get_course()->id,
            'picture'             => new user_picture($user),
            'userfullnamedisplay' => fullname($user, has_capability('moodle/site:viewfullnames', $this->get_context())),
            'lastaccess'          => get_string('never'),
            'lastcourseaccess'    => get_string('never'),
        );

        foreach ($extrafields as $field) {
            $details[$field] = $user->{$field};
        }

                if (!empty($user->lastaccess)) {
            $details['lastaccess'] = format_time($now - $user->lastaccess);
        }

                if (!empty($user->lastcourseaccess)) {
            $details['lastcourseaccess'] = format_time($now - $user->lastcourseaccess);
        }
        return $details;
    }

    public function get_manual_enrol_buttons() {
        $plugins = $this->get_enrolment_plugins(true);         $buttons = array();
        foreach ($plugins as $plugin) {
            $newbutton = $plugin->get_manual_enrol_button($this);
            if (is_array($newbutton)) {
                $buttons += $newbutton;
            } else if ($newbutton instanceof enrol_user_button) {
                $buttons[] = $newbutton;
            }
        }
        return $buttons;
    }

    public function has_instance($enrolpluginname) {
                foreach ($this->get_enrolment_instances() as $instance) {
            if ($instance->enrol == $enrolpluginname) {
                return true;
            }
        }
        return false;
    }

    
    public function get_filtered_enrolment_plugin() {
        $instances = $this->get_enrolment_instances();
        $plugins = $this->get_enrolment_plugins(false);

        if (empty($this->instancefilter) || !array_key_exists($this->instancefilter, $instances)) {
            return false;
        }

        $instance = $instances[$this->instancefilter];
        return $plugins[$instance->enrol];
    }

    
    public function get_users_enrolments(array $userids) {
        global $DB;

        $instances = $this->get_enrolment_instances();
        $plugins = $this->get_enrolment_plugins(false);

        if  (!empty($this->instancefilter)) {
            $instancesql = ' = :instanceid';
            $instanceparams = array('instanceid' => $this->instancefilter);
        } else {
            list($instancesql, $instanceparams) = $DB->get_in_or_equal(array_keys($instances), SQL_PARAMS_NAMED, 'instanceid0000');
        }

        $userfields = user_picture::fields('u');
        list($idsql, $idparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid0000');

        list($sort, $sortparams) = users_order_by_sql('u');

        $sql = "SELECT ue.id AS ueid, ue.status, ue.enrolid, ue.userid, ue.timestart, ue.timeend, ue.modifierid, ue.timecreated, ue.timemodified, $userfields
                  FROM {user_enrolments} ue
             LEFT JOIN {user} u ON u.id = ue.userid
                 WHERE ue.enrolid $instancesql AND
                       u.id $idsql
              ORDER BY $sort";

        $rs = $DB->get_recordset_sql($sql, $idparams + $instanceparams + $sortparams);
        $users = array();
        foreach ($rs as $ue) {
            $user = user_picture::unalias($ue);
            $ue->id = $ue->ueid;
            unset($ue->ueid);
            if (!array_key_exists($user->id, $users)) {
                $user->enrolments = array();
                $users[$user->id] = $user;
            }
            $ue->enrolmentinstance = $instances[$ue->enrolid];
            $ue->enrolmentplugin = $plugins[$ue->enrolmentinstance->enrol];
            $users[$user->id]->enrolments[$ue->id] = $ue;
        }
        $rs->close();
        return $users;
    }
}


class enrol_user_button extends single_button {

    
    protected $jsyuimodules = array();

    
    protected $jsinitcalls = array();

    
    protected $jsstrings = array();

    
    public function __construct(moodle_url $url, $label, $method = 'post') {
        static $count = 0;
        $count ++;
        parent::__construct($url, $label, $method);
        $this->class = 'singlebutton enrolusersbutton';
        $this->formid = 'enrolusersbutton-'.$count;
    }

    
    public function require_yui_module($modules, $function, array $arguments = null, $galleryversion = null, $ondomready = false) {
        if ($galleryversion != null) {
            debugging('The galleryversion parameter to yui_module has been deprecated since Moodle 2.3.', DEBUG_DEVELOPER);
        }

        $js = new stdClass;
        $js->modules = (array)$modules;
        $js->function = $function;
        $js->arguments = $arguments;
        $js->ondomready = $ondomready;
        $this->jsyuimodules[] = $js;
    }

    
    public function require_js_init_call($function, array $extraarguments = null, $ondomready = false, array $module = null) {
        $js = new stdClass;
        $js->function = $function;
        $js->extraarguments = $extraarguments;
        $js->ondomready = $ondomready;
        $js->module = $module;
        $this->jsinitcalls[] = $js;
    }

    
    public function strings_for_js($identifiers, $component = 'moodle', $a = null) {
        $string = new stdClass;
        $string->identifiers = (array)$identifiers;
        $string->component = $component;
        $string->a = $a;
        $this->jsstrings[] = $string;
    }

    
    public function initialise_js(moodle_page $page) {
        foreach ($this->jsyuimodules as $js) {
            $page->requires->yui_module($js->modules, $js->function, $js->arguments, null, $js->ondomready);
        }
        foreach ($this->jsinitcalls as $js) {
            $page->requires->js_init_call($js->function, $js->extraarguments, $js->ondomready, $js->module);
        }
        foreach ($this->jsstrings as $string) {
            $page->requires->strings_for_js($string->identifiers, $string->component, $string->a);
        }
    }
}


class user_enrolment_action implements renderable {

    
    protected $icon;

    
    protected $title;

    
    protected $url;

    
    protected $attributes = array();

    
    public function __construct(pix_icon $icon, $title, $url, array $attributes = null) {
        $this->icon = $icon;
        $this->title = $title;
        $this->url = new moodle_url($url);
        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
        $this->attributes['title'] = $title;
    }

    
    public function get_icon() {
        return $this->icon;
    }

    
    public function get_title() {
        return $this->title;
    }

    
    public function get_url() {
        return $this->url;
    }

    
    public function get_attributes() {
        return $this->attributes;
    }
}

class enrol_ajax_exception extends moodle_exception {
    
    public function __construct($errorcode, $link = '', $a = NULL, $debuginfo = null) {
        parent::__construct($errorcode, 'enrol', $link, $a, $debuginfo);
    }
}


abstract class enrol_bulk_enrolment_operation {

    
    protected $manager;

    
    protected $plugin;

    
    public function __construct(course_enrolment_manager $manager, enrol_plugin $plugin = null) {
        $this->manager = $manager;
        $this->plugin = $plugin;
    }

    
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        return false;
    }

    
    abstract public function get_title();

    
    abstract public function get_identifier();

    
    abstract public function process(course_enrolment_manager $manager, array $users, stdClass $properties);
}
