<?php



defined('MOODLE_INTERNAL') || die();


class enrol_mnet_mnetservice_enrol {

    
    public function available_courses() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/filelib.php');

        if (!$client = get_mnet_remote_client()) {
            die('Callable via XML-RPC only');
        }

                        $sql = "SELECT c.id AS remoteid, c.fullname, c.shortname, c.idnumber, c.summary, c.summaryformat,
                       c.sortorder, c.startdate, cat.id AS cat_id, cat.name AS cat_name,
                       cat.description AS cat_description, cat.descriptionformat AS cat_descriptionformat,
                       e.cost, e.currency, e.roleid AS defaultroleid, r.name AS defaultrolename,
                       e.customint1
                  FROM {enrol} e
            INNER JOIN {course} c ON c.id = e.courseid
            INNER JOIN {course_categories} cat ON cat.id = c.category
            INNER JOIN {role} r ON r.id = e.roleid
                 WHERE e.enrol = 'mnet'
                       AND (e.customint1 = 0 OR e.customint1 = ?)
                       AND c.visible = 1
              ORDER BY cat.sortorder, c.sortorder, c.shortname";

        $rs = $DB->get_recordset_sql($sql, array($client->id));

        $courses = array();
        foreach ($rs as $course) {
                        if (empty($courses[$course->remoteid]) or ($course->customint1 > 0)) {
                unset($course->customint1);                 $context = context_course::instance($course->remoteid);
                                $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', false);
                $courses[$course->remoteid] = $course;
            }
        }
        $rs->close();

        return array_values($courses);     }

    
    public function user_enrolments() {
        global $CFG, $DB;

        if (!$client = get_mnet_remote_client()) {
            die('Callable via XML-RPC only');
        }
        return array();
    }

    
    public function enrol_user(array $userdata, $courseid) {
        global $CFG, $DB;
        require_once(dirname(__FILE__).'/lib.php');

        if (!$client = get_mnet_remote_client()) {
            die('Callable via XML-RPC only');
        }

        if (empty($userdata['username'])) {
            throw new mnet_server_exception(5021, 'emptyusername', 'enrol_mnet');
        }

                $user = $DB->get_record('user', array('username'=>$userdata['username'], 'mnethostid'=>$client->id));

        if ($user === false) {
                                    $user = mnet_strip_user((object)$userdata, mnet_fields_to_import($client));
            $user->mnethostid = $client->id;
            $user->auth = 'mnet';
            $user->confirmed = 1;
            try {
                $user->id = $DB->insert_record('user', $user);
            } catch (Exception $e) {
                throw new mnet_server_exception(5011, 'couldnotcreateuser', 'enrol_mnet');
            }
        }

        if (! $course = $DB->get_record('course', array('id'=>$courseid))) {
            throw new mnet_server_exception(5012, 'coursenotfound', 'enrol_mnet');
        }

        $courses = $this->available_courses();
        $isavailable = false;
        foreach ($courses as $available) {
            if ($available->remoteid == $course->id) {
                $isavailable = true;
                break;
            }
        }
        if (!$isavailable) {
            throw new mnet_server_exception(5013, 'courseunavailable', 'enrol_mnet');
        }

                $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'mnet', 'customint1'=>$client->id), '*', IGNORE_MISSING);

        if ($instance === false) {
                        $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'mnet', 'customint1'=>0), '*', IGNORE_MISSING);
        }

        if ($instance === false) {
                        throw new mnet_server_exception(5017, 'noenrolinstance', 'enrol_mnet');
        }

        if (!$enrol = enrol_get_plugin('mnet')) {
            throw new mnet_server_exception(5018, 'couldnotinstantiate', 'enrol_mnet');
        }

        try {
            $enrol->enrol_user($instance, $user->id, $instance->roleid, time());

        } catch (Exception $e) {
            throw new mnet_server_exception(5019, 'couldnotenrol', 'enrol_mnet', $e->getMessage());
        }

        return true;
    }

    
    public function unenrol_user($username, $courseid) {
        global $CFG, $DB;

        if (!$client = get_mnet_remote_client()) {
            die('Callable via XML-RPC only');
        }

        $user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$client->id));

        if ($user === false) {
            throw new mnet_server_exception(5014, 'usernotfound', 'enrol_mnet');
        }

        if (! $course = $DB->get_record('course', array('id'=>$courseid))) {
            throw new mnet_server_exception(5012, 'coursenotfound', 'enrol_mnet');
        }

        $courses = $this->available_courses();
        $isavailable = false;
        foreach ($courses as $available) {
            if ($available->remoteid == $course->id) {
                $isavailable = true;
                break;
            }
        }
        if (!$isavailable) {
                        throw new mnet_server_exception(5013, 'courseunavailable', 'enrol_mnet');
        }

                $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'mnet', 'customint1'=>$client->id), '*', IGNORE_MISSING);

        if ($instance === false) {
                        $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'mnet', 'customint1'=>0), '*', IGNORE_MISSING);
            $instanceforall = true;
        }

        if ($instance === false) {
                        throw new mnet_server_exception(5017, 'noenrolinstance', 'enrol_mnet');
        }

        if (!$enrol = enrol_get_plugin('mnet')) {
            throw new mnet_server_exception(5018, 'couldnotinstantiate', 'enrol_mnet');
        }

        if ($DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$user->id))) {
            try {
                $enrol->unenrol_user($instance, $user->id);

            } catch (Exception $e) {
                throw new mnet_server_exception(5020, 'couldnotunenrol', 'enrol_mnet', $e->getMessage());
            }
        }

        if (empty($instanceforall)) {
                                    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'mnet', 'customint1'=>0), '*', IGNORE_MISSING);

            if ($instance) {
                                
                if ($DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$user->id))) {
                    try {
                        $enrol->unenrol_user($instance, $user->id);

                    } catch (Exception $e) {
                        throw new mnet_server_exception(5020, 'couldnotunenrol', 'enrol_mnet', $e->getMessage());
                    }
                }
            }
        }

        return true;
    }

    
    public function course_enrolments($courseid, $roles=null) {
        global $DB, $CFG;

        if (!$client = get_mnet_remote_client()) {
            die('Callable via XML-RPC only');
        }

        $sql = "SELECT u.username, r.shortname, r.name, e.enrol, ue.timemodified
                  FROM {user_enrolments} ue
                  JOIN {user} u ON ue.userid = u.id
                  JOIN {enrol} e ON ue.enrolid = e.id
                  JOIN {role} r ON e.roleid = r.id
                 WHERE u.mnethostid = :mnethostid
                       AND e.courseid = :courseid
                       AND u.id <> :guestid
                       AND u.confirmed = 1
                       AND u.deleted = 0";
        $params['mnethostid'] = $client->id;
        $params['courseid'] = $courseid;
        $params['guestid'] = $CFG->siteguest;

        if (!is_null($roles)) {
            if (!is_array($roles)) {
                $roles = explode(',', $roles);
            }
            $roles = array_map('trim', $roles);
            list($rsql, $rparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED);
            $sql .= " AND r.shortname $rsql";
            $params = array_merge($params, $rparams);
        }

        list($sort, $sortparams) = users_order_by_sql('u');
        $sql .= " ORDER BY $sort";

        $rs = $DB->get_recordset_sql($sql, array_merge($params, $sortparams));
        $list = array();
        foreach ($rs as $record) {
            $list[] = $record;
        }
        $rs->close();

        return $list;
    }
}
