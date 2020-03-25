<?php



defined('MOODLE_INTERNAL') || die();


class coursecat implements renderable, cacheable_object, IteratorAggregate {
    
    protected static $coursecat0;

    
    protected static $coursecatfields = array(
        'id' => array('id', 0),
        'name' => array('na', ''),
        'idnumber' => array('in', null),
        'description' => null,         'descriptionformat' => null,         'parent' => array('pa', 0),
        'sortorder' => array('so', 0),
        'coursecount' => array('cc', 0),
        'visible' => array('vi', 1),
        'visibleold' => null,         'timemodified' => null,         'depth' => array('dh', 1),
        'path' => array('ph', null),
        'theme' => null,     );

    
    protected $id;

    
    protected $name = '';

    
    protected $idnumber = null;

    
    protected $description = false;

    
    protected $descriptionformat = false;

    
    protected $parent = 0;

    
    protected $sortorder = 0;

    
    protected $coursecount = false;

    
    protected $visible = 1;

    
    protected $visibleold = false;

    
    protected $timemodified = false;

    
    protected $depth = 0;

    
    protected $path = '';

    
    protected $theme = false;

    
    protected $fromcache;

    
    protected $hasmanagecapability = null;

    
    public function __set($name, $value) {
        debugging('Can not change coursecat instance properties!', DEBUG_DEVELOPER);
    }

    
    public function __get($name) {
        global $DB;
        if (array_key_exists($name, self::$coursecatfields)) {
            if ($this->$name === false) {
                                $notretrievedfields = array_diff_key(self::$coursecatfields, array_filter(self::$coursecatfields));
                $record = $DB->get_record('course_categories', array('id' => $this->id),
                        join(',', array_keys($notretrievedfields)), MUST_EXIST);
                foreach ($record as $key => $value) {
                    $this->$key = $value;
                }
            }
            return $this->$name;
        }
        debugging('Invalid coursecat property accessed! '.$name, DEBUG_DEVELOPER);
        return null;
    }

    
    public function __isset($name) {
        if (array_key_exists($name, self::$coursecatfields)) {
            return isset($this->$name);
        }
        return false;
    }

    
    public function __unset($name) {
        debugging('Can not unset coursecat instance properties!', DEBUG_DEVELOPER);
    }

    
    public function getIterator() {
        $ret = array();
        foreach (self::$coursecatfields as $property => $unused) {
            if ($this->$property !== false) {
                $ret[$property] = $this->$property;
            }
        }
        return new ArrayIterator($ret);
    }

    
    protected function __construct(stdClass $record, $fromcache = false) {
        context_helper::preload_from_record($record);
        foreach ($record as $key => $val) {
            if (array_key_exists($key, self::$coursecatfields)) {
                $this->$key = $val;
            }
        }
        $this->fromcache = $fromcache;
    }

    
    public static function get($id, $strictness = MUST_EXIST, $alwaysreturnhidden = false) {
        if (!$id) {
            if (!isset(self::$coursecat0)) {
                $record = new stdClass();
                $record->id = 0;
                $record->visible = 1;
                $record->depth = 0;
                $record->path = '';
                self::$coursecat0 = new coursecat($record);
            }
            return self::$coursecat0;
        }
        $coursecatrecordcache = cache::make('core', 'coursecatrecords');
        $coursecat = $coursecatrecordcache->get($id);
        if ($coursecat === false) {
            if ($records = self::get_records('cc.id = :id', array('id' => $id))) {
                $record = reset($records);
                $coursecat = new coursecat($record);
                                $coursecatrecordcache->set($id, $coursecat);
            }
        }
        if ($coursecat && ($alwaysreturnhidden || $coursecat->is_uservisible())) {
            return $coursecat;
        } else {
            if ($strictness == MUST_EXIST) {
                throw new moodle_exception('unknowncategory');
            }
        }
        return null;
    }

    
    public static function get_many(array $ids) {
        global $DB;
        $coursecatrecordcache = cache::make('core', 'coursecatrecords');
        $categories = $coursecatrecordcache->get_many($ids);
        $toload = array();
        foreach ($categories as $id => $result) {
            if ($result === false) {
                $toload[] = $id;
            }
        }
        if (!empty($toload)) {
            list($where, $params) = $DB->get_in_or_equal($toload, SQL_PARAMS_NAMED);
            $records = self::get_records('cc.id '.$where, $params);
            $toset = array();
            foreach ($records as $record) {
                $categories[$record->id] = new coursecat($record);
                $toset[$record->id] = $categories[$record->id];
            }
            $coursecatrecordcache->set_many($toset);
        }
        return $categories;
    }

    
    public static function get_default() {
        if ($visiblechildren = self::get(0)->get_children()) {
            $defcategory = reset($visiblechildren);
        } else {
            $toplevelcategories = self::get_tree(0);
            $defcategoryid = $toplevelcategories[0];
            $defcategory = self::get($defcategoryid, MUST_EXIST, true);
        }
        return $defcategory;
    }

    
    protected function restore() {
                $newrecord = self::get($this->id, MUST_EXIST, true);
        foreach (self::$coursecatfields as $key => $unused) {
            $this->$key = $newrecord->$key;
        }
    }

    
    public static function create($data, $editoroptions = null) {
        global $DB, $CFG;
        $data = (object)$data;
        $newcategory = new stdClass();

        $newcategory->descriptionformat = FORMAT_MOODLE;
        $newcategory->description = '';
                foreach ($data as $key => $value) {
            if (preg_match("/^description/", $key)) {
                $newcategory->$key = $value;
            }
        }

        if (empty($data->name)) {
            throw new moodle_exception('categorynamerequired');
        }
        if (core_text::strlen($data->name) > 255) {
            throw new moodle_exception('categorytoolong');
        }
        $newcategory->name = $data->name;

                if (isset($data->idnumber)) {
            if (core_text::strlen($data->idnumber) > 100) {
                throw new moodle_exception('idnumbertoolong');
            }
            if (strval($data->idnumber) !== '' && $DB->record_exists('course_categories', array('idnumber' => $data->idnumber))) {
                throw new moodle_exception('categoryidnumbertaken');
            }
            $newcategory->idnumber = $data->idnumber;
        }

        if (isset($data->theme) && !empty($CFG->allowcategorythemes)) {
            $newcategory->theme = $data->theme;
        }

        if (empty($data->parent)) {
            $parent = self::get(0);
        } else {
            $parent = self::get($data->parent, MUST_EXIST, true);
        }
        $newcategory->parent = $parent->id;
        $newcategory->depth = $parent->depth + 1;

                if (isset($data->visible) && !$data->visible) {
                        $newcategory->visible = $newcategory->visibleold = 0;
        } else {
                        $newcategory->visible = $parent->visible;
                        $newcategory->visibleold = 1;
        }

        $newcategory->sortorder = 0;
        $newcategory->timemodified = time();

        $newcategory->id = $DB->insert_record('course_categories', $newcategory);

                $path = $parent->path . '/' . $newcategory->id;
        $DB->set_field('course_categories', 'path', $path, array('id' => $newcategory->id));

                context_coursecat::instance($newcategory->id)->mark_dirty();

        fix_course_sortorder();

                $categorycontext = context_coursecat::instance($newcategory->id);
        if ($editoroptions) {
            $newcategory = file_postupdate_standard_editor($newcategory, 'description', $editoroptions, $categorycontext,
                                                           'coursecat', 'description', 0);

                        $updatedata = new stdClass();
            $updatedata->id = $newcategory->id;
            $updatedata->description = $newcategory->description;
            $updatedata->descriptionformat = $newcategory->descriptionformat;
            $DB->update_record('course_categories', $updatedata);
        }

        $event = \core\event\course_category_created::create(array(
            'objectid' => $newcategory->id,
            'context' => $categorycontext
        ));
        $event->trigger();

        cache_helper::purge_by_event('changesincoursecat');

        return self::get($newcategory->id, MUST_EXIST, true);
    }

    
    public function update($data, $editoroptions = null) {
        global $DB, $CFG;
        if (!$this->id) {
                        return;
        }

        $data = (object)$data;
        $newcategory = new stdClass();
        $newcategory->id = $this->id;

                foreach ($data as $key => $value) {
            if (preg_match("/^description/", $key)) {
                $newcategory->$key = $value;
            }
        }

        if (isset($data->name) && empty($data->name)) {
            throw new moodle_exception('categorynamerequired');
        }

        if (!empty($data->name) && $data->name !== $this->name) {
            if (core_text::strlen($data->name) > 255) {
                throw new moodle_exception('categorytoolong');
            }
            $newcategory->name = $data->name;
        }

        if (isset($data->idnumber) && $data->idnumber !== $this->idnumber) {
            if (core_text::strlen($data->idnumber) > 100) {
                throw new moodle_exception('idnumbertoolong');
            }
            if (strval($data->idnumber) !== '' && $DB->record_exists('course_categories', array('idnumber' => $data->idnumber))) {
                throw new moodle_exception('categoryidnumbertaken');
            }
            $newcategory->idnumber = $data->idnumber;
        }

        if (isset($data->theme) && !empty($CFG->allowcategorythemes)) {
            $newcategory->theme = $data->theme;
        }

        $changes = false;
        if (isset($data->visible)) {
            if ($data->visible) {
                $changes = $this->show_raw();
            } else {
                $changes = $this->hide_raw(0);
            }
        }

        if (isset($data->parent) && $data->parent != $this->parent) {
            if ($changes) {
                cache_helper::purge_by_event('changesincoursecat');
            }
            $parentcat = self::get($data->parent, MUST_EXIST, true);
            $this->change_parent_raw($parentcat);
            fix_course_sortorder();
        }

        $newcategory->timemodified = time();

        $categorycontext = $this->get_context();
        if ($editoroptions) {
            $newcategory = file_postupdate_standard_editor($newcategory, 'description', $editoroptions, $categorycontext,
                                                           'coursecat', 'description', 0);
        }
        $DB->update_record('course_categories', $newcategory);

        $event = \core\event\course_category_updated::create(array(
            'objectid' => $newcategory->id,
            'context' => $categorycontext
        ));
        $event->trigger();

        fix_course_sortorder();
                cache_helper::purge_by_event('changesincoursecat');

                $this->restore();
    }

    
    public function is_uservisible() {
        return !$this->id || $this->visible ||
                has_capability('moodle/category:viewhiddencategories', $this->get_context());
    }

    
    public function get_db_record() {
        global $DB;
        if ($record = $DB->get_record('course_categories', array('id' => $this->id))) {
            return $record;
        } else {
            return (object)convert_to_array($this);
        }
    }

    
    protected static function get_tree($id) {
        global $DB;
        $coursecattreecache = cache::make('core', 'coursecattree');
        $rv = $coursecattreecache->get($id);
        if ($rv !== false) {
            return $rv;
        }
                $sql = "SELECT cc.id, cc.parent, cc.visible
                FROM {course_categories} cc
                ORDER BY cc.sortorder";
        $rs = $DB->get_recordset_sql($sql, array());
        $all = array(0 => array(), '0i' => array());
        $count = 0;
        foreach ($rs as $record) {
            $all[$record->id] = array();
            $all[$record->id. 'i'] = array();
            if (array_key_exists($record->parent, $all)) {
                $all[$record->parent][] = $record->id;
                if (!$record->visible) {
                    $all[$record->parent. 'i'][] = $record->id;
                }
            } else {
                                $all[0][] = $record->id;
                if (!$record->visible) {
                    $all['0i'][] = $record->id;
                }
            }
            $count++;
        }
        $rs->close();
        if (!$count) {
                                                $defcoursecat = self::create(array('name' => get_string('miscellaneous')));
            set_config('defaultrequestcategory', $defcoursecat->id);
            $all[0] = array($defcoursecat->id);
            $all[$defcoursecat->id] = array();
            $count++;
        }
                $all['countall'] = $count;
        foreach ($all as $key => $children) {
            $coursecattreecache->set($key, $children);
        }
        if (array_key_exists($id, $all)) {
            return $all[$id];
        }
                return array();
    }

    
    public static function count_all() {
        return self::get_tree('countall');
    }

    
    protected static function get_records($whereclause, $params) {
        global $DB;
                $fields = array_keys(array_filter(self::$coursecatfields));
        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT cc.". join(',cc.', $fields). ", $ctxselect
                FROM {course_categories} cc
                JOIN {context} ctx ON cc.id = ctx.instanceid AND ctx.contextlevel = :contextcoursecat
                WHERE ". $whereclause." ORDER BY cc.sortorder";
        return $DB->get_records_sql($sql,
                array('contextcoursecat' => CONTEXT_COURSECAT) + $params);
    }

    
    public static function role_assignment_changed($roleid, $context) {
        global $CFG, $DB;

        if ($context->contextlevel > CONTEXT_COURSE) {
                        return;
        }

        if (!$CFG->coursecontact || !in_array($roleid, explode(',', $CFG->coursecontact))) {
                        return;
        }

                $cache = cache::make('core', 'coursecontacts');
        if ($context->contextlevel == CONTEXT_COURSE) {
            $cache->delete($context->instanceid);
        } else if ($context->contextlevel == CONTEXT_SYSTEM) {
            $cache->purge();
        } else {
            $sql = "SELECT ctx.instanceid
                    FROM {context} ctx
                    WHERE ctx.path LIKE ? AND ctx.contextlevel = ?";
            $params = array($context->path . '/%', CONTEXT_COURSE);
            if ($courses = $DB->get_fieldset_sql($sql, $params)) {
                $cache->delete_many($courses);
            }
        }
    }

    
    public static function user_enrolment_changed($courseid, $userid,
            $status, $timestart = null, $timeend = null) {
        $cache = cache::make('core', 'coursecontacts');
        $contacts = $cache->get($courseid);
        if ($contacts === false) {
                        return;
        }
        $enrolmentactive = ($status == 0) &&
                (!$timestart || $timestart < time()) &&
                (!$timeend || $timeend > time());
        if (!$enrolmentactive) {
            $isincontacts = false;
            foreach ($contacts as $contact) {
                if ($contact->id == $userid) {
                    $isincontacts = true;
                }
            }
            if (!$isincontacts) {
                                                return;
            }
        }
                                                        $cache->delete($courseid);
    }

    
    public static function preload_course_contacts(&$courses) {
        global $CFG, $DB;
        if (empty($courses) || empty($CFG->coursecontact)) {
            return;
        }
        $managerroles = explode(',', $CFG->coursecontact);
        $cache = cache::make('core', 'coursecontacts');
        $cacheddata = $cache->get_many(array_keys($courses));
        $courseids = array();
        foreach (array_keys($courses) as $id) {
            if ($cacheddata[$id] !== false) {
                $courses[$id]->managers = $cacheddata[$id];
            } else {
                $courseids[] = $id;
            }
        }

                if (empty($courseids)) {
            return;
        }

                $allcontexts = array();
        foreach ($courseids as $id) {
            $context = context_course::instance($id);
            $courses[$id]->managers = array();
            foreach (preg_split('|/|', $context->path, 0, PREG_SPLIT_NO_EMPTY) as $ctxid) {
                if (!isset($allcontexts[$ctxid])) {
                    $allcontexts[$ctxid] = array();
                }
                $allcontexts[$ctxid][] = $id;
            }
        }

                list($sql1, $params1) = $DB->get_in_or_equal(array_keys($allcontexts), SQL_PARAMS_NAMED, 'ctxid');
        list($sql2, $params2) = $DB->get_in_or_equal($managerroles, SQL_PARAMS_NAMED, 'rid');
        list($sort, $sortparams) = users_order_by_sql('u');
        $notdeleted = array('notdeleted'=>0);
        $allnames = get_all_user_name_fields(true, 'u');
        $sql = "SELECT ra.contextid, ra.id AS raid,
                       r.id AS roleid, r.name AS rolename, r.shortname AS roleshortname,
                       rn.name AS rolecoursealias, u.id, u.username, $allnames
                  FROM {role_assignments} ra
                  JOIN {user} u ON ra.userid = u.id
                  JOIN {role} r ON ra.roleid = r.id
             LEFT JOIN {role_names} rn ON (rn.contextid = ra.contextid AND rn.roleid = r.id)
                WHERE  ra.contextid ". $sql1." AND ra.roleid ". $sql2." AND u.deleted = :notdeleted
             ORDER BY r.sortorder, $sort";
        $rs = $DB->get_recordset_sql($sql, $params1 + $params2 + $notdeleted + $sortparams);
        $checkenrolments = array();
        foreach ($rs as $ra) {
            foreach ($allcontexts[$ra->contextid] as $id) {
                $courses[$id]->managers[$ra->raid] = $ra;
                if (!isset($checkenrolments[$id])) {
                    $checkenrolments[$id] = array();
                }
                $checkenrolments[$id][] = $ra->id;
            }
        }
        $rs->close();

                $enrolleduserids = self::ensure_users_enrolled($checkenrolments);
        foreach ($checkenrolments as $id => $userids) {
            if (empty($enrolleduserids[$id])) {
                $courses[$id]->managers = array();
            } else if ($notenrolled = array_diff($userids, $enrolleduserids[$id])) {
                foreach ($courses[$id]->managers as $raid => $ra) {
                    if (in_array($ra->id, $notenrolled)) {
                        unset($courses[$id]->managers[$raid]);
                    }
                }
            }
        }

                $values = array();
        foreach ($courseids as $id) {
            $values[$id] = $courses[$id]->managers;
        }
        $cache->set_many($values);
    }

    
    protected static function ensure_users_enrolled($courseusers) {
        global $DB;
                $maxcoursesinquery = 20;
        if (count($courseusers) > $maxcoursesinquery) {
            $rv = array();
            for ($offset = 0; $offset < count($courseusers); $offset += $maxcoursesinquery) {
                $chunk = array_slice($courseusers, $offset, $maxcoursesinquery, true);
                $rv = $rv + self::ensure_users_enrolled($chunk);
            }
            return $rv;
        }

                $sql = "SELECT DISTINCT e.courseid, ue.userid
          FROM {user_enrolments} ue
          JOIN {enrol} e ON e.id = ue.enrolid
          WHERE ue.status = :active
            AND e.status = :enabled
            AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
        $now = round(time(), -2);         $params = array('enabled' => ENROL_INSTANCE_ENABLED,
            'active' => ENROL_USER_ACTIVE,
            'now1' => $now, 'now2' => $now);
        $cnt = 0;
        $subsqls = array();
        $enrolled = array();
        foreach ($courseusers as $id => $userids) {
            $enrolled[$id] = array();
            if (count($userids)) {
                list($sql2, $params2) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid'.$cnt.'_');
                $subsqls[] = "(e.courseid = :courseid$cnt AND ue.userid ".$sql2.")";
                $params = $params + array('courseid'.$cnt => $id) + $params2;
                $cnt++;
            }
        }
        if (count($subsqls)) {
            $sql .= "AND (". join(' OR ', $subsqls).")";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $record) {
                $enrolled[$record->courseid][] = $record->userid;
            }
            $rs->close();
        }
        return $enrolled;
    }

    
    protected static function get_course_records($whereclause, $params, $options, $checkvisibility = false) {
        global $DB;
        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
        $fields = array('c.id', 'c.category', 'c.sortorder',
                        'c.shortname', 'c.fullname', 'c.idnumber',
                        'c.startdate', 'c.visible', 'c.cacherev');
        if (!empty($options['summary'])) {
            $fields[] = 'c.summary';
            $fields[] = 'c.summaryformat';
        } else {
            $fields[] = $DB->sql_substr('c.summary', 1, 1). ' as hassummary';
        }
        $sql = "SELECT ". join(',', $fields). ", $ctxselect
                FROM {course} c
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = :contextcourse
                WHERE ". $whereclause." ORDER BY c.sortorder";
        $list = $DB->get_records_sql($sql,
                array('contextcourse' => CONTEXT_COURSE) + $params);

        if ($checkvisibility) {
                        foreach ($list as $course) {
                if (isset($list[$course->id]->hassummary)) {
                    $list[$course->id]->hassummary = strlen($list[$course->id]->hassummary) > 0;
                }
                if (empty($course->visible)) {
                                        context_helper::preload_from_record($course);
                    if (!has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                        unset($list[$course->id]);
                    }
                }
            }
        }

                if (!empty($options['coursecontacts'])) {
            self::preload_course_contacts($list);
        }
        return $list;
    }

    
    protected function get_not_visible_children_ids() {
        global $DB;
        $coursecatcache = cache::make('core', 'coursecat');
        if (($invisibleids = $coursecatcache->get('ic'. $this->id)) === false) {
                        $hidden = self::get_tree($this->id.'i');
            $invisibleids = array();
            if ($hidden) {
                                list($sql, $params) = $DB->get_in_or_equal($hidden, SQL_PARAMS_NAMED, 'id');
                $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
                $contexts = $DB->get_records_sql("SELECT $ctxselect FROM {context} ctx
                    WHERE ctx.contextlevel = :contextcoursecat AND ctx.instanceid ".$sql,
                        array('contextcoursecat' => CONTEXT_COURSECAT) + $params);
                foreach ($contexts as $record) {
                    context_helper::preload_from_record($record);
                }
                                foreach ($hidden as $id) {
                    if (!has_capability('moodle/category:viewhiddencategories', context_coursecat::instance($id))) {
                        $invisibleids[] = $id;
                    }
                }
            }
            $coursecatcache->set('ic'. $this->id, $invisibleids);
        }
        return $invisibleids;
    }

    
    protected static function sort_records(&$records, $sortfields) {
        if (empty($records)) {
            return;
        }
                if (array_key_exists('displayname', $sortfields)) {
            foreach ($records as $key => $record) {
                if (!isset($record->displayname)) {
                    $records[$key]->displayname = get_course_display_name_for_list($record);
                }
            }
        }
                if (count($sortfields) == 1) {
            $property = key($sortfields);
            if (in_array($property, array('sortorder', 'id', 'visible', 'parent', 'depth'))) {
                $sortflag = core_collator::SORT_NUMERIC;
            } else if (in_array($property, array('idnumber', 'displayname', 'name', 'shortname', 'fullname'))) {
                $sortflag = core_collator::SORT_STRING;
            } else {
                $sortflag = core_collator::SORT_REGULAR;
            }
            core_collator::asort_objects_by_property($records, $property, $sortflag);
            if ($sortfields[$property] < 0) {
                $records = array_reverse($records, true);
            }
            return;
        }
        $records = coursecat_sortable_records::sort($records, $sortfields);
    }

    
    public function get_children($options = array()) {
        global $DB;
        $coursecatcache = cache::make('core', 'coursecat');

                if (!empty($options['sort']) && is_array($options['sort'])) {
            $sortfields = $options['sort'];
        } else {
            $sortfields = array('sortorder' => 1);
        }
        $limit = null;
        if (!empty($options['limit']) && (int)$options['limit']) {
            $limit = (int)$options['limit'];
        }
        $offset = 0;
        if (!empty($options['offset']) && (int)$options['offset']) {
            $offset = (int)$options['offset'];
        }

                $sortedids = $coursecatcache->get('c'. $this->id. ':'.  serialize($sortfields));
        if ($sortedids === false) {
            $sortfieldskeys = array_keys($sortfields);
            if ($sortfieldskeys[0] === 'sortorder') {
                                                $sortedids = self::get_tree($this->id);
                if ($sortedids && ($invisibleids = $this->get_not_visible_children_ids())) {
                    $sortedids = array_diff($sortedids, $invisibleids);
                    if ($sortfields['sortorder'] == -1) {
                        $sortedids = array_reverse($sortedids, true);
                    }
                }
            } else {
                                if ($invisibleids = $this->get_not_visible_children_ids()) {
                    list($sql, $params) = $DB->get_in_or_equal($invisibleids, SQL_PARAMS_NAMED, 'id', false);
                    $records = self::get_records('cc.parent = :parent AND cc.id '. $sql,
                            array('parent' => $this->id) + $params);
                } else {
                    $records = self::get_records('cc.parent = :parent', array('parent' => $this->id));
                }
                self::sort_records($records, $sortfields);
                $sortedids = array_keys($records);
            }
            $coursecatcache->set('c'. $this->id. ':'.serialize($sortfields), $sortedids);
        }

        if (empty($sortedids)) {
            return array();
        }

                if ($offset || $limit) {
            $sortedids = array_slice($sortedids, $offset, $limit);
        }
        if (isset($records)) {
                        if ($offset || $limit) {
                $records = array_slice($records, $offset, $limit, true);
            }
        } else {
            list($sql, $params) = $DB->get_in_or_equal($sortedids, SQL_PARAMS_NAMED, 'id');
            $records = self::get_records('cc.id '. $sql, array('parent' => $this->id) + $params);
        }

        $rv = array();
        foreach ($sortedids as $id) {
            if (isset($records[$id])) {
                $rv[$id] = new coursecat($records[$id]);
            }
        }
        return $rv;
    }

    
    public static function has_manage_capability_on_any() {
        return self::has_capability_on_any('moodle/category:manage');
    }

    
    public static function has_capability_on_any($capabilities) {
        global $DB;
        if (!isloggedin() || isguestuser()) {
            return false;
        }

        if (!is_array($capabilities)) {
            $capabilities = array($capabilities);
        }
        $keys = array();
        foreach ($capabilities as $capability) {
            $keys[$capability] = sha1($capability);
        }

        
        $cache = cache::make('core', 'coursecat');
        $hascapability = $cache->get_many($keys);
        $needtoload = false;
        foreach ($hascapability as $capability) {
            if ($capability === '1') {
                return true;
            } else if ($capability === false) {
                $needtoload = true;
            }
        }
        if ($needtoload === false) {
                        return false;
        }

        $haskey = null;
        $fields = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT ctx.instanceid AS categoryid, $fields
                      FROM {context} ctx
                     WHERE contextlevel = :contextlevel
                  ORDER BY depth ASC";
        $params = array('contextlevel' => CONTEXT_COURSECAT);
        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $context) {
            context_helper::preload_from_record($context);
            $context = context_coursecat::instance($context->categoryid);
            foreach ($capabilities as $capability) {
                if (has_capability($capability, $context)) {
                    $haskey = $capability;
                    break 2;
                }
            }
        }
        $recordset->close();
        if ($haskey === null) {
            $data = array();
            foreach ($keys as $key) {
                $data[$key] = '0';
            }
            $cache->set_many($data);
            return false;
        } else {
            $cache->set($haskey, '1');
            return true;
        }
    }

    
    public static function can_resort_any() {
        return self::has_manage_capability_on_any();
    }

    
    public static function can_change_parent_any() {
        return self::has_manage_capability_on_any();
    }

    
    public function get_children_count() {
        $sortedids = self::get_tree($this->id);
        $invisibleids = $this->get_not_visible_children_ids();
        return count($sortedids) - count($invisibleids);
    }

    
    public function has_children() {
        $allchildren = self::get_tree($this->id);
        return !empty($allchildren);
    }

    
    public function has_courses() {
        global $DB;
        return $DB->record_exists_sql("select 1 from {course} where category = ?",
                array($this->id));
    }

    
    public static function search_courses($search, $options = array(), $requiredcapabilities = array()) {
        global $DB;
        $offset = !empty($options['offset']) ? $options['offset'] : 0;
        $limit = !empty($options['limit']) ? $options['limit'] : null;
        $sortfields = !empty($options['sort']) ? $options['sort'] : array('sortorder' => 1);

        $coursecatcache = cache::make('core', 'coursecat');
        $cachekey = 's-'. serialize(
            $search + array('sort' => $sortfields) + array('requiredcapabilities' => $requiredcapabilities)
        );
        $cntcachekey = 'scnt-'. serialize($search);

        $ids = $coursecatcache->get($cachekey);
        if ($ids !== false) {
                        $ids = array_slice($ids, $offset, $limit);
            $courses = array();
            if (!empty($ids)) {
                list($sql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id');
                $records = self::get_course_records("c.id ". $sql, $params, $options);
                                if (!empty($options['coursecontacts'])) {
                    self::preload_course_contacts($records);
                }
                                if (!empty($options['idonly'])) {
                    return array_keys($records);
                }
                                foreach ($ids as $id) {
                    $courses[$id] = new course_in_list($records[$id]);
                }
            }
            return $courses;
        }

        $preloadcoursecontacts = !empty($options['coursecontacts']);
        unset($options['coursecontacts']);

                if (!isset($search['search'])) {
            $search['search'] = '';
        }

        if (empty($search['blocklist']) && empty($search['modulelist']) && empty($search['tagid'])) {
                        $searchterms = preg_split('|\s+|', trim($search['search']), 0, PREG_SPLIT_NO_EMPTY);

            $courselist = get_courses_search($searchterms, 'c.sortorder ASC', 0, 9999999, $totalcount, $requiredcapabilities);
            self::sort_records($courselist, $sortfields);
            $coursecatcache->set($cachekey, array_keys($courselist));
            $coursecatcache->set($cntcachekey, $totalcount);
            $records = array_slice($courselist, $offset, $limit, true);
        } else {
            if (!empty($search['blocklist'])) {
                                $blockname = $DB->get_field('block', 'name', array('id' => $search['blocklist']));
                $where = 'ctx.id in (SELECT distinct bi.parentcontextid FROM {block_instances} bi
                    WHERE bi.blockname = :blockname)';
                $params = array('blockname' => $blockname);
            } else if (!empty($search['modulelist'])) {
                                $where = "c.id IN (SELECT DISTINCT module.course ".
                        "FROM {".$search['modulelist']."} module)";
                $params = array();
            } else if (!empty($search['tagid'])) {
                                $where = "c.id IN (SELECT t.itemid ".
                        "FROM {tag_instance} t WHERE t.tagid = :tagid AND t.itemtype = :itemtype AND t.component = :component)";
                $params = array('tagid' => $search['tagid'], 'itemtype' => 'course', 'component' => 'core');
                if (!empty($search['ctx'])) {
                    $rec = isset($search['rec']) ? $search['rec'] : true;
                    $parentcontext = context::instance_by_id($search['ctx']);
                    if ($parentcontext->contextlevel == CONTEXT_SYSTEM && $rec) {
                                                                    } else if ($rec) {
                                                $where .= ' AND ctx.path LIKE :contextpath';
                        $params['contextpath'] = $parentcontext->path . '%';
                    } else if ($parentcontext->contextlevel == CONTEXT_COURSECAT) {
                                                $where .= ' AND c.category = :category';
                        $params['category'] = $parentcontext->instanceid;
                    } else {
                                                $where = '1=0';
                    }
                }
            } else {
                debugging('No criteria is specified while searching courses', DEBUG_DEVELOPER);
                return array();
            }
            $courselist = self::get_course_records($where, $params, $options, true);
            if (!empty($requiredcapabilities)) {
                foreach ($courselist as $key => $course) {
                    context_helper::preload_from_record($course);
                    $coursecontext = context_course::instance($course->id);
                    if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
                        unset($courselist[$key]);
                    }
                }
            }
            self::sort_records($courselist, $sortfields);
            $coursecatcache->set($cachekey, array_keys($courselist));
            $coursecatcache->set($cntcachekey, count($courselist));
            $records = array_slice($courselist, $offset, $limit, true);
        }

                if (!empty($preloadcoursecontacts)) {
            self::preload_course_contacts($records);
        }
                if (!empty($options['idonly'])) {
            return array_keys($records);
        }
                $courses = array();
        foreach ($records as $record) {
            $courses[$record->id] = new course_in_list($record);
        }
        return $courses;
    }

    
    public static function search_courses_count($search, $options = array(), $requiredcapabilities = array()) {
        $coursecatcache = cache::make('core', 'coursecat');
        $cntcachekey = 'scnt-'. serialize($search) . serialize($requiredcapabilities);
        if (($cnt = $coursecatcache->get($cntcachekey)) === false) {
                        unset($options['offset']);
            unset($options['limit']);
            unset($options['summary']);
            unset($options['coursecontacts']);
            $options['idonly'] = true;
            $courses = self::search_courses($search, $options, $requiredcapabilities);
            $cnt = count($courses);
        }
        return $cnt;
    }

    
    public function get_courses($options = array()) {
        global $DB;
        $recursive = !empty($options['recursive']);
        $offset = !empty($options['offset']) ? $options['offset'] : 0;
        $limit = !empty($options['limit']) ? $options['limit'] : null;
        $sortfields = !empty($options['sort']) ? $options['sort'] : array('sortorder' => 1);

                        if (!$this->is_uservisible() || (!$this->id && !$recursive)) {
            return array();
        }

        $coursecatcache = cache::make('core', 'coursecat');
        $cachekey = 'l-'. $this->id. '-'. (!empty($options['recursive']) ? 'r' : '').
                 '-'. serialize($sortfields);
        $cntcachekey = 'lcnt-'. $this->id. '-'. (!empty($options['recursive']) ? 'r' : '');

                $ids = $coursecatcache->get($cachekey);
        if ($ids !== false) {
                        $ids = array_slice($ids, $offset, $limit);
            $courses = array();
            if (!empty($ids)) {
                list($sql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id');
                $records = self::get_course_records("c.id ". $sql, $params, $options);
                                if (!empty($options['coursecontacts'])) {
                    self::preload_course_contacts($records);
                }
                                if (!empty($options['idonly'])) {
                    return array_keys($records);
                }
                                foreach ($ids as $id) {
                    $courses[$id] = new course_in_list($records[$id]);
                }
            }
            return $courses;
        }

                $where = 'c.id <> :siteid';
        $params = array('siteid' => SITEID);
        if ($recursive) {
            if ($this->id) {
                $context = context_coursecat::instance($this->id);
                $where .= ' AND ctx.path like :path';
                $params['path'] = $context->path. '/%';
            }
        } else {
            $where .= ' AND c.category = :categoryid';
            $params['categoryid'] = $this->id;
        }
                $list = $this->get_course_records($where, $params, array_diff_key($options, array('coursecontacts' => 1)), true);

                self::sort_records($list, $sortfields);
        $coursecatcache->set($cachekey, array_keys($list));
        $coursecatcache->set($cntcachekey, count($list));

                $courses = array();
        if (isset($list)) {
            if ($offset || $limit) {
                $list = array_slice($list, $offset, $limit, true);
            }
                        if (!empty($options['coursecontacts'])) {
                self::preload_course_contacts($list);
            }
                        if (!empty($options['idonly'])) {
                return array_keys($list);
            }
                        foreach ($list as $record) {
                $courses[$record->id] = new course_in_list($record);
            }
        }
        return $courses;
    }

    
    public function get_courses_count($options = array()) {
        $cntcachekey = 'lcnt-'. $this->id. '-'. (!empty($options['recursive']) ? 'r' : '');
        $coursecatcache = cache::make('core', 'coursecat');
        if (($cnt = $coursecatcache->get($cntcachekey)) === false) {
                        unset($options['offset']);
            unset($options['limit']);
            unset($options['summary']);
            unset($options['coursecontacts']);
            $options['idonly'] = true;
            $courses = $this->get_courses($options);
            $cnt = count($courses);
        }
        return $cnt;
    }

    
    public function can_delete() {
        if (!$this->has_manage_capability()) {
            return false;
        }
        return $this->parent_has_manage_capability();
    }

    
    public function can_delete_full() {
        global $DB;
        if (!$this->id) {
                        return false;
        }

        $context = $this->get_context();
        if (!$this->is_uservisible() ||
                !has_capability('moodle/category:manage', $context)) {
            return false;
        }

                $sql = context_helper::get_preload_record_columns_sql('ctx');
        $childcategories = $DB->get_records_sql('SELECT c.id, c.visible, '. $sql.
            ' FROM {context} ctx '.
            ' JOIN {course_categories} c ON c.id = ctx.instanceid'.
            ' WHERE ctx.path like ? AND ctx.contextlevel = ?',
                array($context->path. '/%', CONTEXT_COURSECAT));
        foreach ($childcategories as $childcat) {
            context_helper::preload_from_record($childcat);
            $childcontext = context_coursecat::instance($childcat->id);
            if ((!$childcat->visible && !has_capability('moodle/category:viewhiddencategories', $childcontext)) ||
                    !has_capability('moodle/category:manage', $childcontext)) {
                return false;
            }
        }

                $sql = context_helper::get_preload_record_columns_sql('ctx');
        $coursescontexts = $DB->get_records_sql('SELECT ctx.instanceid AS courseid, '.
                    $sql. ' FROM {context} ctx '.
                    'WHERE ctx.path like :pathmask and ctx.contextlevel = :courselevel',
                array('pathmask' => $context->path. '/%',
                    'courselevel' => CONTEXT_COURSE));
        foreach ($coursescontexts as $ctxrecord) {
            context_helper::preload_from_record($ctxrecord);
            if (!can_delete_course($ctxrecord->courseid)) {
                return false;
            }
        }

        return true;
    }

    
    public function delete_full($showfeedback = true) {
        global $CFG, $DB;

        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->libdir.'/questionlib.php');
        require_once($CFG->dirroot.'/cohort/lib.php');

                $settimeout = core_php_time_limit::raise();

                if ($pluginsfunction = get_plugins_with_function('pre_course_category_delete')) {
            $category = $this->get_db_record();
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    $pluginfunction($category);
                }
            }
        }

        $deletedcourses = array();

                $children = $DB->get_records('course_categories', array('parent' => $this->id), 'sortorder ASC');
        foreach ($children as $record) {
            $coursecat = new coursecat($record);
            $deletedcourses += $coursecat->delete_full($showfeedback);
        }

        if ($courses = $DB->get_records('course', array('category' => $this->id), 'sortorder ASC')) {
            foreach ($courses as $course) {
                if (!delete_course($course, false)) {
                    throw new moodle_exception('cannotdeletecategorycourse', '', '', $course->shortname);
                }
                $deletedcourses[] = $course;
            }
        }

                cohort_delete_category($this);

                grade_course_category_delete($this->id, 0, $showfeedback);
        if (!question_delete_course_category($this, 0, $showfeedback)) {
            throw new moodle_exception('cannotdeletecategoryquestions', '', '', $this->get_formatted_name());
        }

                $DB->delete_records('course_categories', array('id' => $this->id));

        $coursecatcontext = context_coursecat::instance($this->id);
        $coursecatcontext->delete();

        cache_helper::purge_by_event('changesincoursecat');

                
        $event = \core\event\course_category_deleted::create(array(
            'objectid' => $this->id,
            'context' => $coursecatcontext,
            'other' => array('name' => $this->name)
        ));
        $event->set_coursecat($this);
        $event->trigger();

                if ($this->id == $CFG->defaultrequestcategory) {
            set_config('defaultrequestcategory', $DB->get_field('course_categories', 'MIN(id)', array('parent' => 0)));
        }
        return $deletedcourses;
    }

    
    public function move_content_targets_list() {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        $context = $this->get_context();
        if (!$this->is_uservisible() ||
                !has_capability('moodle/category:manage', $context)) {
                                    return array();
        }

        $testcaps = array();
                if ($this->has_courses()) {
            $testcaps[] = 'moodle/course:create';
        }
                if ($this->has_children() || question_context_has_any_questions($context)) {
            $testcaps[] = 'moodle/category:manage';
        }
        if (!empty($testcaps)) {
                        return self::make_categories_list($testcaps, $this->id);
        }

                return array();
    }

    
    public function can_move_content_to($newcatid) {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        $context = $this->get_context();
        if (!$this->is_uservisible() ||
                !has_capability('moodle/category:manage', $context)) {
            return false;
        }
        $testcaps = array();
                if ($this->has_courses()) {
            $testcaps[] = 'moodle/course:create';
        }
                if ($this->has_children() || question_context_has_any_questions($context)) {
            $testcaps[] = 'moodle/category:manage';
        }
        if (!empty($testcaps)) {
            return has_all_capabilities($testcaps, context_coursecat::instance($newcatid));
        }

                return true;
    }

    
    public function delete_move($newparentid, $showfeedback = false) {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->libdir.'/questionlib.php');
        require_once($CFG->dirroot.'/cohort/lib.php');

                        $newparentcat = self::get($newparentid, MUST_EXIST, true);
        $catname = $this->get_formatted_name();
        $children = $this->get_children();
        $params = array('category' => $this->id);
        $coursesids = $DB->get_fieldset_select('course', 'id', 'category = :category ORDER BY sortorder ASC', $params);
        $context = $this->get_context();

        if ($children) {
            foreach ($children as $childcat) {
                $childcat->change_parent_raw($newparentcat);
                                $event = \core\event\course_category_updated::create(array(
                    'objectid' => $childcat->id,
                    'context' => $childcat->get_context()
                ));
                $event->set_legacy_logdata(array(SITEID, 'category', 'move', 'editcategory.php?id=' . $childcat->id,
                    $childcat->id));
                $event->trigger();
            }
            fix_course_sortorder();
        }

        if ($coursesids) {
            if (!move_courses($coursesids, $newparentid)) {
                if ($showfeedback) {
                    echo $OUTPUT->notification("Error moving courses");
                }
                return false;
            }
            if ($showfeedback) {
                echo $OUTPUT->notification(get_string('coursesmovedout', '', $catname), 'notifysuccess');
            }
        }

                cohort_delete_category($this);

                grade_course_category_delete($this->id, $newparentid, $showfeedback);
        if (!question_delete_course_category($this, $newparentcat, $showfeedback)) {
            if ($showfeedback) {
                echo $OUTPUT->notification(get_string('errordeletingquestionsfromcategory', 'question', $catname), 'notifysuccess');
            }
            return false;
        }

                $DB->delete_records('course_categories', array('id' => $this->id));
        $context->delete();

                
        $event = \core\event\course_category_deleted::create(array(
            'objectid' => $this->id,
            'context' => $context,
            'other' => array('name' => $this->name)
        ));
        $event->set_coursecat($this);
        $event->trigger();

        cache_helper::purge_by_event('changesincoursecat');

        if ($showfeedback) {
            echo $OUTPUT->notification(get_string('coursecategorydeleted', '', $catname), 'notifysuccess');
        }

                if ($this->id == $CFG->defaultrequestcategory) {
            set_config('defaultrequestcategory', $DB->get_field('course_categories', 'MIN(id)', array('parent' => 0)));
        }
        return true;
    }

    
    public function can_change_parent($newparentcat) {
        if (!has_capability('moodle/category:manage', $this->get_context())) {
            return false;
        }
        if (is_object($newparentcat)) {
            $newparentcat = self::get($newparentcat->id, IGNORE_MISSING);
        } else {
            $newparentcat = self::get((int)$newparentcat, IGNORE_MISSING);
        }
        if (!$newparentcat) {
            return false;
        }
        if ($newparentcat->id == $this->id || in_array($this->id, $newparentcat->get_parents())) {
                        return false;
        }
        if ($newparentcat->id) {
            return has_capability('moodle/category:manage', context_coursecat::instance($newparentcat->id));
        } else {
            return has_capability('moodle/category:manage', context_system::instance());
        }
    }

    
    protected function change_parent_raw(coursecat $newparentcat) {
        global $DB;

        $context = $this->get_context();

        $hidecat = false;
        if (empty($newparentcat->id)) {
            $DB->set_field('course_categories', 'parent', 0, array('id' => $this->id));
            $newparent = context_system::instance();
        } else {
            if ($newparentcat->id == $this->id || in_array($this->id, $newparentcat->get_parents())) {
                                throw new moodle_exception('cannotmovecategory');
            }
            $DB->set_field('course_categories', 'parent', $newparentcat->id, array('id' => $this->id));
            $newparent = context_coursecat::instance($newparentcat->id);

            if (!$newparentcat->visible and $this->visible) {
                                                $hidecat = true;
            }
        }
        $this->parent = $newparentcat->id;

        $context->update_moved($newparent);

                $DB->set_field('course_categories', 'sortorder', MAX_COURSES_IN_CATEGORY*MAX_COURSE_CATEGORIES, array('id' => $this->id));

        if ($hidecat) {
            fix_course_sortorder();
            $this->restore();
                                    $this->hide_raw(1);
        }
    }

    
    public function change_parent($newparentcat) {
                if (is_object($newparentcat)) {
            $newparentcat = self::get($newparentcat->id, MUST_EXIST, true);
        } else {
            $newparentcat = self::get((int)$newparentcat, MUST_EXIST, true);
        }
        if ($newparentcat->id != $this->parent) {
            $this->change_parent_raw($newparentcat);
            fix_course_sortorder();
            cache_helper::purge_by_event('changesincoursecat');
            $this->restore();

            $event = \core\event\course_category_updated::create(array(
                'objectid' => $this->id,
                'context' => $this->get_context()
            ));
            $event->set_legacy_logdata(array(SITEID, 'category', 'move', 'editcategory.php?id=' . $this->id, $this->id));
            $event->trigger();
        }
    }

    
    protected function hide_raw($visibleold = 0) {
        global $DB;
        $changes = false;

                if ($this->id && $this->__get('visibleold') != $visibleold) {
            $this->visibleold = $visibleold;
            $DB->set_field('course_categories', 'visibleold', $visibleold, array('id' => $this->id));
            $changes = true;
        }
        if (!$this->visible || !$this->id) {
                        return $changes;
        }

        $this->visible = 0;
        $DB->set_field('course_categories', 'visible', 0, array('id'=>$this->id));
                $DB->execute("UPDATE {course} SET visibleold = visible WHERE category = ?", array($this->id));
        $DB->set_field('course', 'visible', 0, array('category' => $this->id));
                if ($subcats = $DB->get_records_select('course_categories', "path LIKE ?", array("$this->path/%"), 'id, visible')) {
            foreach ($subcats as $cat) {
                $DB->set_field('course_categories', 'visibleold', $cat->visible, array('id' => $cat->id));
                $DB->set_field('course_categories', 'visible', 0, array('id' => $cat->id));
                $DB->execute("UPDATE {course} SET visibleold = visible WHERE category = ?", array($cat->id));
                $DB->set_field('course', 'visible', 0, array('category' => $cat->id));
            }
        }
        return true;
    }

    
    public function hide() {
        if ($this->hide_raw(0)) {
            cache_helper::purge_by_event('changesincoursecat');

            $event = \core\event\course_category_updated::create(array(
                'objectid' => $this->id,
                'context' => $this->get_context()
            ));
            $event->set_legacy_logdata(array(SITEID, 'category', 'hide', 'editcategory.php?id=' . $this->id, $this->id));
            $event->trigger();
        }
    }

    
    protected function show_raw() {
        global $DB;

        if ($this->visible) {
                        return false;
        }

        $this->visible = 1;
        $this->visibleold = 1;
        $DB->set_field('course_categories', 'visible', 1, array('id' => $this->id));
        $DB->set_field('course_categories', 'visibleold', 1, array('id' => $this->id));
        $DB->execute("UPDATE {course} SET visible = visibleold WHERE category = ?", array($this->id));
                if ($subcats = $DB->get_records_select('course_categories', "path LIKE ?", array("$this->path/%"), 'id, visibleold')) {
            foreach ($subcats as $cat) {
                if ($cat->visibleold) {
                    $DB->set_field('course_categories', 'visible', 1, array('id' => $cat->id));
                }
                $DB->execute("UPDATE {course} SET visible = visibleold WHERE category = ?", array($cat->id));
            }
        }
        return true;
    }

    
    public function show() {
        if ($this->show_raw()) {
            cache_helper::purge_by_event('changesincoursecat');

            $event = \core\event\course_category_updated::create(array(
                'objectid' => $this->id,
                'context' => $this->get_context()
            ));
            $event->set_legacy_logdata(array(SITEID, 'category', 'show', 'editcategory.php?id=' . $this->id, $this->id));
            $event->trigger();
        }
    }

    
    public function get_formatted_name($options = array()) {
        if ($this->id) {
            $context = $this->get_context();
            return format_string($this->name, true, array('context' => $context) + $options);
        } else {
            return get_string('top');
        }
    }

    
    public function get_parents() {
        $parents = preg_split('|/|', $this->path, 0, PREG_SPLIT_NO_EMPTY);
        array_pop($parents);
        return $parents;
    }

    
    public static function make_categories_list($requiredcapability = '', $excludeid = 0, $separator = ' / ') {
        global $DB;
        $coursecatcache = cache::make('core', 'coursecat');

                        $currentlang = current_language();
        $basecachekey = $currentlang . '_catlist';
        $baselist = $coursecatcache->get($basecachekey);
        $thislist = false;
        $thiscachekey = null;
        if (!empty($requiredcapability)) {
            $requiredcapability = (array)$requiredcapability;
            $thiscachekey = 'catlist:'. serialize($requiredcapability);
            if ($baselist !== false && ($thislist = $coursecatcache->get($thiscachekey)) !== false) {
                $thislist = preg_split('|,|', $thislist, -1, PREG_SPLIT_NO_EMPTY);
            }
        } else if ($baselist !== false) {
            $thislist = array_keys($baselist);
        }

        if ($baselist === false) {
                        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT cc.id, cc.sortorder, cc.name, cc.visible, cc.parent, cc.path, $ctxselect
                    FROM {course_categories} cc
                    JOIN {context} ctx ON cc.id = ctx.instanceid AND ctx.contextlevel = :contextcoursecat
                    ORDER BY cc.sortorder";
            $rs = $DB->get_recordset_sql($sql, array('contextcoursecat' => CONTEXT_COURSECAT));
            $baselist = array();
            $thislist = array();
            foreach ($rs as $record) {
                                if (!$record->parent || isset($baselist[$record->parent])) {
                    context_helper::preload_from_record($record);
                    $context = context_coursecat::instance($record->id);
                    if (!$record->visible && !has_capability('moodle/category:viewhiddencategories', $context)) {
                                                continue;
                    }
                    $baselist[$record->id] = array(
                        'name' => format_string($record->name, true, array('context' => $context)),
                        'path' => $record->path
                    );
                    if (!empty($requiredcapability) && !has_all_capabilities($requiredcapability, $context)) {
                                                continue;
                    }
                    $thislist[] = $record->id;
                }
            }
            $rs->close();
            $coursecatcache->set($basecachekey, $baselist);
            if (!empty($requiredcapability)) {
                $coursecatcache->set($thiscachekey, join(',', $thislist));
            }
        } else if ($thislist === false) {
                        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT ctx.instanceid AS id, $ctxselect
                    FROM {context} ctx WHERE ctx.contextlevel = :contextcoursecat";
            $contexts = $DB->get_records_sql($sql, array('contextcoursecat' => CONTEXT_COURSECAT));
            $thislist = array();
            foreach (array_keys($baselist) as $id) {
                context_helper::preload_from_record($contexts[$id]);
                if (has_all_capabilities($requiredcapability, context_coursecat::instance($id))) {
                    $thislist[] = $id;
                }
            }
            $coursecatcache->set($thiscachekey, join(',', $thislist));
        }

                $names = array();
        foreach ($thislist as $id) {
            $path = preg_split('|/|', $baselist[$id]['path'], -1, PREG_SPLIT_NO_EMPTY);
            if (!$excludeid || !in_array($excludeid, $path)) {
                $namechunks = array();
                foreach ($path as $parentid) {
                    $namechunks[] = $baselist[$parentid]['name'];
                }
                $names[$id] = join($separator, $namechunks);
            }
        }
        return $names;
    }

    
    public function prepare_to_cache() {
        $a = array();
        foreach (self::$coursecatfields as $property => $cachedirectives) {
            if ($cachedirectives !== null) {
                list($shortname, $defaultvalue) = $cachedirectives;
                if ($this->$property !== $defaultvalue) {
                    $a[$shortname] = $this->$property;
                }
            }
        }
        $context = $this->get_context();
        $a['xi'] = $context->id;
        $a['xp'] = $context->path;
        return $a;
    }

    
    public static function wake_from_cache($a) {
        $record = new stdClass;
        foreach (self::$coursecatfields as $property => $cachedirectives) {
            if ($cachedirectives !== null) {
                list($shortname, $defaultvalue) = $cachedirectives;
                if (array_key_exists($shortname, $a)) {
                    $record->$property = $a[$shortname];
                } else {
                    $record->$property = $defaultvalue;
                }
            }
        }
        $record->ctxid = $a['xi'];
        $record->ctxpath = $a['xp'];
        $record->ctxdepth = $record->depth + 1;
        $record->ctxlevel = CONTEXT_COURSECAT;
        $record->ctxinstance = $record->id;
        return new coursecat($record, true);
    }

    
    public static function can_create_top_level_category() {
        return has_capability('moodle/category:manage', context_system::instance());
    }

    
    public function get_context() {
        if ($this->id === 0) {
                        return context_system::instance();
        } else {
            return context_coursecat::instance($this->id);
        }
    }

    
    public function has_manage_capability() {
        if ($this->hasmanagecapability === null) {
            $this->hasmanagecapability = has_capability('moodle/category:manage', $this->get_context());
        }
        return $this->hasmanagecapability;
    }

    
    public function parent_has_manage_capability() {
        return has_capability('moodle/category:manage', get_category_or_system_context($this->parent));
    }

    
    public function can_create_subcategory() {
        return $this->has_manage_capability();
    }

    
    public function can_resort_subcategories() {
        return $this->has_manage_capability() && !$this->get_not_visible_children_ids();
    }

    
    public function can_resort_courses() {
        return $this->has_manage_capability() && $this->coursecount == $this->get_courses_count();
    }

    
    public function can_change_sortorder() {
        return $this->id && $this->get_parent_coursecat()->can_resort_subcategories();
    }

    
    public function can_create_course() {
        return has_capability('moodle/course:create', $this->get_context());
    }

    
    public function can_edit() {
        return $this->has_manage_capability();
    }

    
    public function can_review_roles() {
        return has_capability('moodle/role:assign', $this->get_context());
    }

    
    public function can_review_permissions() {
        return has_any_capability(array(
            'moodle/role:assign',
            'moodle/role:safeoverride',
            'moodle/role:override',
            'moodle/role:assign'
        ), $this->get_context());
    }

    
    public function can_review_cohorts() {
        return has_any_capability(array('moodle/cohort:view', 'moodle/cohort:manage'), $this->get_context());
    }

    
    public function can_review_filters() {
        return has_capability('moodle/filter:manage', $this->get_context()) &&
               count(filter_get_available_in_context($this->get_context()))>0;
    }

    
    public function can_change_visibility() {
        return $this->parent_has_manage_capability();
    }

    
    public function can_move_courses_out_of() {
        return $this->has_manage_capability();
    }

    
    public function can_move_courses_into() {
        return $this->has_manage_capability();
    }

    
    public function can_restore_courses_into() {
        return has_capability('moodle/restore:restorecourse', $this->get_context());
    }

    
    public function resort_subcategories($field, $cleanup = true) {
        global $DB;
        $desc = false;
        if (substr($field, -4) === "desc") {
            $desc = true;
            $field = substr($field, 0, -4);          }
        if ($field !== 'name' && $field !== 'idnumber') {
            throw new coding_exception('Invalid field requested');
        }
        $children = $this->get_children();
        core_collator::asort_objects_by_property($children, $field, core_collator::SORT_NATURAL);
        if (!empty($desc)) {
            $children = array_reverse($children);
        }
        $i = 1;
        foreach ($children as $cat) {
            $i++;
            $DB->set_field('course_categories', 'sortorder', $i, array('id' => $cat->id));
            $i += $cat->coursecount;
        }
        if ($cleanup) {
            self::resort_categories_cleanup();
        }
        return true;
    }

    
    public static function resort_categories_cleanup($includecourses = false) {
                fix_course_sortorder();
        cache_helper::purge_by_event('changesincoursecat');
        if ($includecourses) {
            cache_helper::purge_by_event('changesincourse');
        }
    }

    
    public function resort_courses($field, $cleanup = true) {
        global $DB;
        $desc = false;
        if (substr($field, -4) === "desc") {
            $desc = true;
            $field = substr($field, 0, -4);          }
        if ($field !== 'fullname' && $field !== 'shortname' && $field !== 'idnumber' && $field !== 'timecreated') {
                        throw new coding_exception('Invalid field requested');
        }
        $ctxfields = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT c.id, c.sortorder, c.{$field}, $ctxfields
                  FROM {course} c
             LEFT JOIN {context} ctx ON ctx.instanceid = c.id
                 WHERE ctx.contextlevel = :ctxlevel AND
                       c.category = :categoryid";
        $params = array(
            'ctxlevel' => CONTEXT_COURSE,
            'categoryid' => $this->id
        );
        $courses = $DB->get_records_sql($sql, $params);
        if (count($courses) > 0) {
            foreach ($courses as $courseid => $course) {
                context_helper::preload_from_record($course);
                if ($field === 'idnumber') {
                    $course->sortby = $course->idnumber;
                } else {
                                        $options = array(
                        'context' => context_course::instance($course->id)
                    );
                                                                                                                        $course->sortby = strip_tags(format_string($course->$field, true, $options));
                }
                                                $courses[$courseid] = $course;
            }
                        core_collator::asort_objects_by_property($courses, 'sortby', core_collator::SORT_NATURAL);
            if (!empty($desc)) {
                $courses = array_reverse($courses);
            }
            $i = 1;
            foreach ($courses as $course) {
                $DB->set_field('course', 'sortorder', $this->sortorder + $i, array('id' => $course->id));
                $i++;
            }
            if ($cleanup) {
                                fix_course_sortorder();
                cache_helper::purge_by_event('changesincourse');
            }
        }
        return true;
    }

    
    public function change_sortorder_by_one($up) {
        global $DB;
        $params = array($this->sortorder, $this->parent);
        if ($up) {
            $select = 'sortorder < ? AND parent = ?';
            $sort = 'sortorder DESC';
        } else {
            $select = 'sortorder > ? AND parent = ?';
            $sort = 'sortorder ASC';
        }
        fix_course_sortorder();
        $swapcategory = $DB->get_records_select('course_categories', $select, $params, $sort, '*', 0, 1);
        $swapcategory = reset($swapcategory);
        if ($swapcategory) {
            $DB->set_field('course_categories', 'sortorder', $swapcategory->sortorder, array('id' => $this->id));
            $DB->set_field('course_categories', 'sortorder', $this->sortorder, array('id' => $swapcategory->id));
            $this->sortorder = $swapcategory->sortorder;

            $event = \core\event\course_category_updated::create(array(
                'objectid' => $this->id,
                'context' => $this->get_context()
            ));
            $event->set_legacy_logdata(array(SITEID, 'category', 'move', 'management.php?categoryid=' . $this->id,
                $this->id));
            $event->trigger();

                        fix_course_sortorder();
            cache_helper::purge_by_event('changesincoursecat');
            return true;
        }
        return false;
    }

    
    public function get_parent_coursecat() {
        return self::get($this->parent);
    }


    
    public function can_request_course() {
        global $CFG;
        if (empty($CFG->enablecourserequests) || $this->id != $CFG->defaultrequestcategory) {
            return false;
        }
        return !$this->can_create_course() && has_capability('moodle/course:request', $this->get_context());
    }

    
    public static function can_approve_course_requests() {
        global $CFG, $DB;
        if (empty($CFG->enablecourserequests)) {
            return false;
        }
        $context = context_system::instance();
        if (!has_capability('moodle/site:approvecourse', $context)) {
            return false;
        }
        if (!$DB->record_exists('course_request', array())) {
            return false;
        }
        return true;
    }
}


class course_in_list implements IteratorAggregate {

    
    protected $record;

    
    protected $coursecontacts;

    
    protected $canaccess = null;

    
    public function __construct(stdClass $record) {
        context_helper::preload_from_record($record);
        $this->record = new stdClass();
        foreach ($record as $key => $value) {
            $this->record->$key = $value;
        }
    }

    
    public function has_summary() {
        if (isset($this->record->hassummary)) {
            return !empty($this->record->hassummary);
        }
        if (!isset($this->record->summary)) {
                        $this->__get('summary');
        }
        return !empty($this->record->summary);
    }

    
    public function has_course_contacts() {
        if (!isset($this->record->managers)) {
            $courses = array($this->id => &$this->record);
            coursecat::preload_course_contacts($courses);
        }
        return !empty($this->record->managers);
    }

    
    public function get_course_contacts() {
        global $CFG;
        if (empty($CFG->coursecontact)) {
                        return array();
        }
        if ($this->coursecontacts === null) {
            $this->coursecontacts = array();
            $context = context_course::instance($this->id);

            if (!isset($this->record->managers)) {
                                $courses = array($this->id => &$this->record);
                coursecat::preload_course_contacts($courses);
            }

                        $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
            foreach ($this->record->managers as $ruser) {
                if (isset($this->coursecontacts[$ruser->id])) {
                                        continue;
                }
                $user = new stdClass();
                $user = username_load_fields_from_object($user, $ruser, null, array('id', 'username'));
                $role = new stdClass();
                $role->id = $ruser->roleid;
                $role->name = $ruser->rolename;
                $role->shortname = $ruser->roleshortname;
                $role->coursealias = $ruser->rolecoursealias;

                $this->coursecontacts[$user->id] = array(
                    'user' => $user,
                    'role' => $role,
                    'rolename' => role_get_name($role, $context, ROLENAME_ALIAS),
                    'username' => fullname($user, $canviewfullnames)
                );
            }
        }
        return $this->coursecontacts;
    }

    
    public function has_course_overviewfiles() {
        global $CFG;
        if (empty($CFG->courseoverviewfileslimit)) {
            return false;
        }
        $fs = get_file_storage();
        $context = context_course::instance($this->id);
        return !$fs->is_area_empty($context->id, 'course', 'overviewfiles');
    }

    
    public function get_course_overviewfiles() {
        global $CFG;
        if (empty($CFG->courseoverviewfileslimit)) {
            return array();
        }
        require_once($CFG->libdir. '/filestorage/file_storage.php');
        require_once($CFG->dirroot. '/course/lib.php');
        $fs = get_file_storage();
        $context = context_course::instance($this->id);
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        if (count($files)) {
            $overviewfilesoptions = course_overviewfiles_options($this->id);
            $acceptedtypes = $overviewfilesoptions['accepted_types'];
            if ($acceptedtypes !== '*') {
                                require_once($CFG->libdir. '/filelib.php');
                foreach ($files as $key => $file) {
                    if (!file_extension_in_typegroup($file->get_filename(), $acceptedtypes)) {
                        unset($files[$key]);
                    }
                }
            }
            if (count($files) > $CFG->courseoverviewfileslimit) {
                                $files = array_slice($files, 0, $CFG->courseoverviewfileslimit, true);
            }
        }
        return $files;
    }

    
    public function __isset($name) {
        return isset($this->record->$name);
    }

    
    public function __get($name) {
        global $DB;
        if (property_exists($this->record, $name)) {
            return $this->record->$name;
        } else if ($name === 'summary' || $name === 'summaryformat') {
                        $record = $DB->get_record('course', array('id' => $this->record->id), 'summary, summaryformat', MUST_EXIST);
            $this->record->summary = $record->summary;
            $this->record->summaryformat = $record->summaryformat;
            return $this->record->$name;
        } else if (array_key_exists($name, $DB->get_columns('course'))) {
                        $this->record->$name = $DB->get_field('course', $name, array('id' => $this->record->id), MUST_EXIST);
            return $this->record->$name;
        }
        debugging('Invalid course property accessed! '.$name);
        return null;
    }

    
    public function __unset($name) {
        debugging('Can not unset '.get_class($this).' instance properties!');
    }

    
    public function __set($name, $value) {
        debugging('Can not change '.get_class($this).' instance properties!');
    }

    
    public function getIterator() {
        $ret = array('id' => $this->record->id);
        foreach ($this->record as $property => $value) {
            $ret[$property] = $value;
        }
        return new ArrayIterator($ret);
    }

    
    public function get_formatted_name() {
        return format_string(get_course_display_name_for_list($this), true, $this->get_context());
    }

    
    public function get_formatted_fullname() {
        return format_string($this->__get('fullname'), true, $this->get_context());
    }

    
    public function get_formatted_shortname() {
        return format_string($this->__get('shortname'), true, $this->get_context());
    }

    
    public function can_access() {
        if ($this->canaccess === null) {
            $this->canaccess = can_access_course($this->record);
        }
        return $this->canaccess;
    }

    
    public function can_edit() {
        return has_capability('moodle/course:update', $this->get_context());
    }

    
    public function can_change_visibility() {
                return has_all_capabilities(array('moodle/course:visibility', 'moodle/course:viewhiddencourses'), $this->get_context());
    }

    
    public function get_context() {
        return context_course::instance($this->__get('id'));
    }

    
    public function is_uservisible() {
        return $this->visible || has_capability('moodle/course:viewhiddencourses', $this->get_context());
    }

    
    public function can_review_enrolments() {
        return has_capability('moodle/course:enrolreview', $this->get_context());
    }

    
    public function can_delete() {
        return can_delete_course($this->id);
    }

    
    public function can_backup() {
        return has_capability('moodle/backup:backupcourse', $this->get_context());
    }

    
    public function can_restore() {
        return has_capability('moodle/restore:restorecourse', $this->get_context());
    }
}


class coursecat_sortable_records extends ArrayObject {

    
    protected $sortfields = array();

    
    public static function sort(array $records, array $fields) {
        $records = new coursecat_sortable_records($records);
        $records->sortfields = $fields;
        $records->uasort(array($records, 'sort_by_many_fields'));
        return $records->getArrayCopy();
    }

    
    public function sort_by_many_fields($a, $b) {
        foreach ($this->sortfields as $field => $mult) {
                        if (is_null($a->$field) && !is_null($b->$field)) {
                return -$mult;
            }
            if (is_null($b->$field) && !is_null($a->$field)) {
                return $mult;
            }

            if (is_string($a->$field) || is_string($b->$field)) {
                                if ($cmp = strcoll($a->$field, $b->$field)) {
                    return $mult * $cmp;
                }
            } else {
                                if ($a->$field > $b->$field) {
                    return $mult;
                }
                if ($a->$field < $b->$field) {
                    return -$mult;
                }
            }
        }
        return 0;
    }
}
