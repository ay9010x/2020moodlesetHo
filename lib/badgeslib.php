<?php



defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/badges/criteria/award_criteria.php');


define('BADGE_PERPAGE', 50);


define('BADGE_CRITERIA_AGGREGATION_ALL', 1);


define('BADGE_CRITERIA_AGGREGATION_ANY', 2);


define('BADGE_STATUS_INACTIVE', 0);


define('BADGE_STATUS_ACTIVE', 1);


define('BADGE_STATUS_INACTIVE_LOCKED', 2);


define('BADGE_STATUS_ACTIVE_LOCKED', 3);


define('BADGE_STATUS_ARCHIVED', 4);


define('BADGE_TYPE_SITE', 1);


define('BADGE_TYPE_COURSE', 2);


define('BADGE_MESSAGE_NEVER', 0);
define('BADGE_MESSAGE_ALWAYS', 1);
define('BADGE_MESSAGE_DAILY', 2);
define('BADGE_MESSAGE_WEEKLY', 3);
define('BADGE_MESSAGE_MONTHLY', 4);


define('BADGE_BACKPACKURL', 'https://backpack.openbadges.org');


class badge {
    
    public $id;

    
    public $name;
    public $description;
    public $timecreated;
    public $timemodified;
    public $usercreated;
    public $usermodified;
    public $issuername;
    public $issuerurl;
    public $issuercontact;
    public $expiredate;
    public $expireperiod;
    public $type;
    public $courseid;
    public $message;
    public $messagesubject;
    public $attachment;
    public $notification;
    public $status = 0;
    public $nextcron;

    
    public $criteria = array();

    
    public function __construct($badgeid) {
        global $DB;
        $this->id = $badgeid;

        $data = $DB->get_record('badge', array('id' => $badgeid));

        if (empty($data)) {
            print_error('error:nosuchbadge', 'badges', $badgeid);
        }

        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }

        $this->criteria = self::get_criteria();
    }

    
    public function get_context() {
        if ($this->type == BADGE_TYPE_SITE) {
            return context_system::instance();
        } else if ($this->type == BADGE_TYPE_COURSE) {
            return context_course::instance($this->courseid);
        } else {
            debugging('Something is wrong...');
        }
    }

    
    public static function get_aggregation_methods() {
        return array(
                BADGE_CRITERIA_AGGREGATION_ALL => get_string('all', 'badges'),
                BADGE_CRITERIA_AGGREGATION_ANY => get_string('any', 'badges'),
        );
    }

    
    public function get_accepted_criteria() {
        $criteriatypes = array();

        if ($this->type == BADGE_TYPE_COURSE) {
            $criteriatypes = array(
                    BADGE_CRITERIA_TYPE_OVERALL,
                    BADGE_CRITERIA_TYPE_MANUAL,
                    BADGE_CRITERIA_TYPE_COURSE,
                    BADGE_CRITERIA_TYPE_ACTIVITY
            );
        } else if ($this->type == BADGE_TYPE_SITE) {
            $criteriatypes = array(
                    BADGE_CRITERIA_TYPE_OVERALL,
                    BADGE_CRITERIA_TYPE_MANUAL,
                    BADGE_CRITERIA_TYPE_COURSESET,
                    BADGE_CRITERIA_TYPE_PROFILE,
            );
        }

        return $criteriatypes;
    }

    
    public function save() {
        global $DB;

        $fordb = new stdClass();
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
        }
        unset($fordb->criteria);

        $fordb->timemodified = time();
        if ($DB->update_record_raw('badge', $fordb)) {
            return true;
        } else {
            throw new moodle_exception('error:save', 'badges');
            return false;
        }
    }

    
    public function make_clone() {
        global $DB, $USER;

        $fordb = new stdClass();
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
        }

        $fordb->name = get_string('copyof', 'badges', $this->name);
        $fordb->status = BADGE_STATUS_INACTIVE;
        $fordb->usercreated = $USER->id;
        $fordb->usermodified = $USER->id;
        $fordb->timecreated = time();
        $fordb->timemodified = time();
        unset($fordb->id);

        if ($fordb->notification > 1) {
            $fordb->nextcron = badges_calculate_message_schedule($fordb->notification);
        }

        $criteria = $fordb->criteria;
        unset($fordb->criteria);

        if ($new = $DB->insert_record('badge', $fordb, true)) {
            $newbadge = new badge($new);

                        $fs = get_file_storage();
            if ($file = $fs->get_file($this->get_context()->id, 'badges', 'badgeimage', $this->id, '/', 'f1.png')) {
                if ($imagefile = $file->copy_content_to_temp()) {
                    badges_process_badge_image($newbadge, $imagefile);
                }
            }

                        foreach ($this->criteria as $crit) {
                $crit->make_clone($new);
            }

            return $new;
        } else {
            throw new moodle_exception('error:clone', 'badges');
            return false;
        }
    }

    
    public function is_active() {
        if (($this->status == BADGE_STATUS_ACTIVE) ||
            ($this->status == BADGE_STATUS_ACTIVE_LOCKED)) {
            return true;
        }
        return false;
    }

    
    public function get_status_name() {
        return get_string('badgestatus_' . $this->status, 'badges');
    }

    
    public function set_status($status = 0) {
        $this->status = $status;
        $this->save();
    }

    
    public function is_locked() {
        if (($this->status == BADGE_STATUS_ACTIVE_LOCKED) ||
                ($this->status == BADGE_STATUS_INACTIVE_LOCKED)) {
            return true;
        }
        return false;
    }

    
    public function has_awards() {
        global $DB;
        $awarded = $DB->record_exists_sql('SELECT b.uniquehash
                    FROM {badge_issued} b INNER JOIN {user} u ON b.userid = u.id
                    WHERE b.badgeid = :badgeid AND u.deleted = 0', array('badgeid' => $this->id));

        return $awarded;
    }

    
    public function get_awards() {
        global $DB;

        $awards = $DB->get_records_sql(
                'SELECT b.userid, b.dateissued, b.uniquehash, u.firstname, u.lastname
                    FROM {badge_issued} b INNER JOIN {user} u
                        ON b.userid = u.id
                    WHERE b.badgeid = :badgeid AND u.deleted = 0', array('badgeid' => $this->id));

        return $awards;
    }

    
    public function is_issued($userid) {
        global $DB;
        return $DB->record_exists('badge_issued', array('badgeid' => $this->id, 'userid' => $userid));
    }

    
    public function issue($userid, $nobake = false) {
        global $DB, $CFG;

        $now = time();
        $issued = new stdClass();
        $issued->badgeid = $this->id;
        $issued->userid = $userid;
        $issued->uniquehash = sha1(rand() . $userid . $this->id . $now);
        $issued->dateissued = $now;

        if ($this->can_expire()) {
            $issued->dateexpire = $this->calculate_expiry($now);
        } else {
            $issued->dateexpire = null;
        }

                        $issued->visible = get_user_preferences('badgeprivacysetting', 1, $userid);

        $result = $DB->insert_record('badge_issued', $issued, true);

        if ($result) {
                        $eventdata = array (
                'context' => $this->get_context(),
                'objectid' => $this->id,
                'relateduserid' => $userid,
                'other' => array('dateexpire' => $issued->dateexpire, 'badgeissuedid' => $result)
            );
            \core\event\badge_awarded::create($eventdata)->trigger();

                        if ($this->status == BADGE_STATUS_ACTIVE) {
                $this->set_status(BADGE_STATUS_ACTIVE_LOCKED);
            }

                        $compl = $this->get_criteria_completions($userid);
            foreach ($compl as $c) {
                $obj = new stdClass();
                $obj->id = $c->id;
                $obj->issuedid = $result;
                $DB->update_record('badge_criteria_met', $obj, true);
            }

            if (!$nobake) {
                                $pathhash = badges_bake($issued->uniquehash, $this->id, $userid, true);

                                badges_notify_badge_award($this, $userid, $issued->uniquehash, $pathhash);
            }
        }
    }

    
    public function review_all_criteria() {
        global $DB, $CFG;
        $awards = 0;

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        foreach ($this->criteria as $crit) {
                        if ($crit->criteriatype == BADGE_CRITERIA_TYPE_OVERALL) {
                continue;
            }

            list($extrajoin, $extrawhere, $extraparams) = $crit->get_completed_criteria_sql();
                        if ($this->type == BADGE_TYPE_SITE) {
                $sql = "SELECT DISTINCT u.id, bi.badgeid
                        FROM {user} u
                        {$extrajoin}
                        LEFT JOIN {badge_issued} bi
                            ON u.id = bi.userid AND bi.badgeid = :badgeid
                        WHERE bi.badgeid IS NULL AND u.id != :guestid AND u.deleted = 0 " . $extrawhere;
                $params = array_merge(array('badgeid' => $this->id, 'guestid' => $CFG->siteguest), $extraparams);
                $toearn = $DB->get_fieldset_sql($sql, $params);
            } else {
                                                $earned = $DB->get_fieldset_select('badge_issued', 'userid AS id', 'badgeid = :badgeid', array('badgeid' => $this->id));
                $wheresql = '';
                $earnedparams = array();
                if (!empty($earned)) {
                    list($earnedsql, $earnedparams) = $DB->get_in_or_equal($earned, SQL_PARAMS_NAMED, 'u', false);
                    $wheresql = ' WHERE u.id ' . $earnedsql;
                }
                list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->get_context(), 'moodle/badges:earnbadge', 0, true);
                $sql = "SELECT DISTINCT u.id
                        FROM {user} u
                        {$extrajoin}
                        JOIN ({$enrolledsql}) je ON je.id = u.id " . $wheresql . $extrawhere;
                $params = array_merge($enrolledparams, $earnedparams, $extraparams);
                $toearn = $DB->get_fieldset_sql($sql, $params);
            }

            foreach ($toearn as $uid) {
                $reviewoverall = false;
                if ($crit->review($uid, true)) {
                    $crit->mark_complete($uid);
                    if ($this->criteria[BADGE_CRITERIA_TYPE_OVERALL]->method == BADGE_CRITERIA_AGGREGATION_ANY) {
                        $this->criteria[BADGE_CRITERIA_TYPE_OVERALL]->mark_complete($uid);
                        $this->issue($uid);
                        $awards++;
                    } else {
                        $reviewoverall = true;
                    }
                } else {
                                        $reviewoverall = false;
                }
                                if ($reviewoverall && $this->criteria[BADGE_CRITERIA_TYPE_OVERALL]->review($uid)) {
                    $this->criteria[BADGE_CRITERIA_TYPE_OVERALL]->mark_complete($uid);
                    $this->issue($uid);
                    $awards++;
                }
            }
        }

        return $awards;
    }

    
    public function get_criteria_completions($userid) {
        global $DB;
        $completions = array();
        $sql = "SELECT bcm.id, bcm.critid
                FROM {badge_criteria_met} bcm
                    INNER JOIN {badge_criteria} bc ON bcm.critid = bc.id
                WHERE bc.badgeid = :badgeid AND bcm.userid = :userid ";
        $completions = $DB->get_records_sql($sql, array('badgeid' => $this->id, 'userid' => $userid));

        return $completions;
    }

    
    public function has_criteria() {
        if (count($this->criteria) > 0) {
            return true;
        }
        return false;
    }

    
    public function get_criteria() {
        global $DB;
        $criteria = array();

        if ($records = (array)$DB->get_records('badge_criteria', array('badgeid' => $this->id))) {
            foreach ($records as $record) {
                $criteria[$record->criteriatype] = award_criteria::build((array)$record);
            }
        }

        return $criteria;
    }

    
    public function get_aggregation_method($criteriatype = 0) {
        global $DB;
        $params = array('badgeid' => $this->id, 'criteriatype' => $criteriatype);
        $aggregation = $DB->get_field('badge_criteria', 'method', $params, IGNORE_MULTIPLE);

        if (!$aggregation) {
            return BADGE_CRITERIA_AGGREGATION_ALL;
        }

        return $aggregation;
    }

    
    public function can_expire() {
        if ($this->expireperiod || $this->expiredate) {
            return true;
        }
        return false;
    }

    
    public function calculate_expiry($timestamp) {
        $expiry = null;

        if (isset($this->expiredate)) {
            $expiry = $this->expiredate;
        } else if (isset($this->expireperiod)) {
            $expiry = $timestamp + $this->expireperiod;
        }

        return $expiry;
    }

    
    public function has_manual_award_criteria() {
        foreach ($this->criteria as $criterion) {
            if ($criterion->criteriatype == BADGE_CRITERIA_TYPE_MANUAL) {
                return true;
            }
        }
        return false;
    }

    
    public function delete($archive = true) {
        global $DB;

        if ($archive) {
            $this->status = BADGE_STATUS_ARCHIVED;
            $this->save();
            return;
        }

        $fs = get_file_storage();

                        $awards = $this->get_awards();
        foreach ($awards as $award) {
            $usercontext = context_user::instance($award->userid);
            $fs->delete_area_files($usercontext->id, 'badges', 'userbadge', $this->id);
        }
        $DB->delete_records('badge_issued', array('badgeid' => $this->id));

                $criteria = $this->get_criteria();
        foreach ($criteria as $criterion) {
            $criterion->delete();
        }

                $badgecontext = $this->get_context();
        $fs->delete_area_files($badgecontext->id, 'badges', 'badgeimage', $this->id);

                $DB->delete_records('badge', array('id' => $this->id));
    }
}


function badges_notify_badge_award(badge $badge, $userid, $issued, $filepathhash) {
    global $CFG, $DB;

    $admin = get_admin();
    $userfrom = new stdClass();
    $userfrom->id = $admin->id;
    $userfrom->email = !empty($CFG->badges_defaultissuercontact) ? $CFG->badges_defaultissuercontact : $admin->email;
    foreach (get_all_user_name_fields() as $addname) {
        $userfrom->$addname = !empty($CFG->badges_defaultissuername) ? '' : $admin->$addname;
    }
    $userfrom->firstname = !empty($CFG->badges_defaultissuername) ? $CFG->badges_defaultissuername : $admin->firstname;
    $userfrom->maildisplay = true;

    $issuedlink = html_writer::link(new moodle_url('/badges/badge.php', array('hash' => $issued)), $badge->name);
    $userto = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

    $params = new stdClass();
    $params->badgename = $badge->name;
    $params->username = fullname($userto);
    $params->badgelink = $issuedlink;
    $message = badge_message_from_template($badge->message, $params);
    $plaintext = html_to_text($message);

        $eventdata = new stdClass();
    $eventdata->component         = 'moodle';
    $eventdata->name              = 'badgerecipientnotice';
    $eventdata->userfrom          = $userfrom;
    $eventdata->userto            = $userto;
    $eventdata->notification      = 1;
    $eventdata->subject           = $badge->messagesubject;
    $eventdata->fullmessage       = $plaintext;
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml   = $message;
    $eventdata->smallmessage      = '';

        if (!empty($CFG->allowattachments) && $badge->attachment && is_string($filepathhash)) {
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($filepathhash);
        $eventdata->attachment = $file;
        $eventdata->attachname = str_replace(' ', '_', $badge->name) . ".png";

        message_send($eventdata);
    } else {
        message_send($eventdata);
    }

        if ($badge->notification == 1) {
        $userfrom = core_user::get_noreply_user();
        $userfrom->maildisplay = true;

        $creator = $DB->get_record('user', array('id' => $badge->usercreated), '*', MUST_EXIST);
        $a = new stdClass();
        $a->user = fullname($userto);
        $a->link = $issuedlink;
        $creatormessage = get_string('creatorbody', 'badges', $a);
        $creatorsubject = get_string('creatorsubject', 'badges', $badge->name);

        $eventdata = new stdClass();
        $eventdata->component         = 'moodle';
        $eventdata->name              = 'badgecreatornotice';
        $eventdata->userfrom          = $userfrom;
        $eventdata->userto            = $creator;
        $eventdata->notification      = 1;
        $eventdata->subject           = $creatorsubject;
        $eventdata->fullmessage       = html_to_text($creatormessage);
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml   = $creatormessage;
        $eventdata->smallmessage      = '';

        message_send($eventdata);
        $DB->set_field('badge_issued', 'issuernotified', time(), array('badgeid' => $badge->id, 'userid' => $userid));
    }
}


function badges_calculate_message_schedule($schedule) {
    $nextcron = 0;

    switch ($schedule) {
        case BADGE_MESSAGE_DAILY:
            $nextcron = time() + 60 * 60 * 24;
            break;
        case BADGE_MESSAGE_WEEKLY:
            $nextcron = time() + 60 * 60 * 24 * 7;
            break;
        case BADGE_MESSAGE_MONTHLY:
            $nextcron = time() + 60 * 60 * 24 * 7 * 30;
            break;
    }

    return $nextcron;
}


function badge_message_from_template($message, $params) {
    $msg = $message;
    foreach ($params as $key => $value) {
        $msg = str_replace("%$key%", $value, $msg);
    }

    return $msg;
}


function badges_get_badges($type, $courseid = 0, $sort = '', $dir = '', $page = 0, $perpage = BADGE_PERPAGE, $user = 0) {
    global $DB;
    $records = array();
    $params = array();
    $where = "b.status != :deleted AND b.type = :type ";
    $params['deleted'] = BADGE_STATUS_ARCHIVED;

    $userfields = array('b.id, b.name, b.status');
    $usersql = "";
    if ($user != 0) {
        $userfields[] = 'bi.dateissued';
        $userfields[] = 'bi.uniquehash';
        $usersql = " LEFT JOIN {badge_issued} bi ON b.id = bi.badgeid AND bi.userid = :userid ";
        $params['userid'] = $user;
        $where .= " AND (b.status = 1 OR b.status = 3) ";
    }
    $fields = implode(', ', $userfields);

    if ($courseid != 0 ) {
        $where .= "AND b.courseid = :courseid ";
        $params['courseid'] = $courseid;
    }

    $sorting = (($sort != '' && $dir != '') ? 'ORDER BY ' . $sort . ' ' . $dir : '');
    $params['type'] = $type;

    $sql = "SELECT $fields FROM {badge} b $usersql WHERE $where $sorting";
    $records = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

    $badges = array();
    foreach ($records as $r) {
        $badge = new badge($r->id);
        $badges[$r->id] = $badge;
        if ($user != 0) {
            $badges[$r->id]->dateissued = $r->dateissued;
            $badges[$r->id]->uniquehash = $r->uniquehash;
        } else {
            $badges[$r->id]->awards = $DB->count_records_sql('SELECT COUNT(b.userid)
                                        FROM {badge_issued} b INNER JOIN {user} u ON b.userid = u.id
                                        WHERE b.badgeid = :badgeid AND u.deleted = 0', array('badgeid' => $badge->id));
            $badges[$r->id]->statstring = $badge->get_status_name();
        }
    }
    return $badges;
}


function badges_get_user_badges($userid, $courseid = 0, $page = 0, $perpage = 0, $search = '', $onlypublic = false) {
    global $CFG, $DB;

    $params = array(
        'userid' => $userid
    );
    $sql = 'SELECT
                bi.uniquehash,
                bi.dateissued,
                bi.dateexpire,
                bi.id as issuedid,
                bi.visible,
                u.email,
                b.*
            FROM
                {badge} b,
                {badge_issued} bi,
                {user} u
            WHERE b.id = bi.badgeid
                AND u.id = bi.userid
                AND bi.userid = :userid';

    if (!empty($search)) {
        $sql .= ' AND (' . $DB->sql_like('b.name', ':search', false) . ') ';
        $params['search'] = '%'.$DB->sql_like_escape($search).'%';
    }
    if ($onlypublic) {
        $sql .= ' AND (bi.visible = 1) ';
    }

    if (empty($CFG->badges_allowcoursebadges)) {
        $sql .= ' AND b.courseid IS NULL';
    } else if ($courseid != 0) {
        $sql .= ' AND (b.courseid = :courseid) ';
        $params['courseid'] = $courseid;
    }
    $sql .= ' ORDER BY bi.dateissued DESC';
    $badges = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

    return $badges;
}


function badges_add_course_navigation(navigation_node $coursenode, stdClass $course) {
    global $CFG, $SITE;

    $coursecontext = context_course::instance($course->id);
    $isfrontpage = (!$coursecontext || $course->id == $SITE->id);
    $canmanage = has_any_capability(array('moodle/badges:viewawarded',
                                          'moodle/badges:createbadge',
                                          'moodle/badges:awardbadge',
                                          'moodle/badges:configurecriteria',
                                          'moodle/badges:configuremessages',
                                          'moodle/badges:configuredetails',
                                          'moodle/badges:deletebadge'), $coursecontext);

    if (!empty($CFG->enablebadges) && !empty($CFG->badges_allowcoursebadges) && !$isfrontpage && $canmanage) {
        $coursenode->add(get_string('coursebadges', 'badges'), null,
                navigation_node::TYPE_CONTAINER, null, 'coursebadges',
                new pix_icon('i/badge', get_string('coursebadges', 'badges')));

        $url = new moodle_url('/badges/index.php', array('type' => BADGE_TYPE_COURSE, 'id' => $course->id));

        $coursenode->get('coursebadges')->add(get_string('managebadges', 'badges'), $url,
            navigation_node::TYPE_SETTING, null, 'coursebadges');

        if (has_capability('moodle/badges:createbadge', $coursecontext)) {
            $url = new moodle_url('/badges/newbadge.php', array('type' => BADGE_TYPE_COURSE, 'id' => $course->id));

            $coursenode->get('coursebadges')->add(get_string('newbadge', 'badges'), $url,
                    navigation_node::TYPE_SETTING, null, 'newbadge');
        }
    }
}


function badges_award_handle_manual_criteria_review(stdClass $data) {
    $criteria = $data->crit;
    $userid = $data->userid;
    $badge = new badge($criteria->badgeid);

    if (!$badge->is_active() || $badge->is_issued($userid)) {
        return true;
    }

    if ($criteria->review($userid)) {
        $criteria->mark_complete($userid);

        if ($badge->criteria[BADGE_CRITERIA_TYPE_OVERALL]->review($userid)) {
            $badge->criteria[BADGE_CRITERIA_TYPE_OVERALL]->mark_complete($userid);
            $badge->issue($userid);
        }
    }

    return true;
}


function badges_process_badge_image(badge $badge, $iconfile) {
    global $CFG, $USER;
    require_once($CFG->libdir. '/gdlib.php');

    if (!empty($CFG->gdversion)) {
        process_new_icon($badge->get_context(), 'badges', 'badgeimage', $badge->id, $iconfile, true);
        @unlink($iconfile);

                $context = context_user::instance($USER->id, MUST_EXIST);
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'user', 'draft');
    }
}


function print_badge_image(badge $badge, stdClass $context, $size = 'small') {
    $fsize = ($size == 'small') ? 'f2' : 'f1';

    $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', $fsize, false);
        $imageurl->param('refresh', rand(1, 10000));
    $attributes = array('src' => $imageurl, 'alt' => s($badge->name), 'class' => 'activatebadge');

    return html_writer::empty_tag('img', $attributes);
}


function badges_bake($hash, $badgeid, $userid = 0, $pathhash = false) {
    global $CFG, $USER;
    require_once(dirname(dirname(__FILE__)) . '/badges/lib/bakerlib.php');

    $badge = new badge($badgeid);
    $badge_context = $badge->get_context();
    $userid = ($userid) ? $userid : $USER->id;
    $user_context = context_user::instance($userid);

    $fs = get_file_storage();
    if (!$fs->file_exists($user_context->id, 'badges', 'userbadge', $badge->id, '/', $hash . '.png')) {
        if ($file = $fs->get_file($badge_context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1.png')) {
            $contents = $file->get_content();

            $filehandler = new PNG_MetaDataHandler($contents);
            $assertion = new moodle_url('/badges/assertion.php', array('b' => $hash));
            if ($filehandler->check_chunks("tEXt", "openbadges")) {
                                $newcontents = $filehandler->add_chunks("tEXt", "openbadges", $assertion->out(false));
                $fileinfo = array(
                        'contextid' => $user_context->id,
                        'component' => 'badges',
                        'filearea' => 'userbadge',
                        'itemid' => $badge->id,
                        'filepath' => '/',
                        'filename' => $hash . '.png',
                );

                                $newfile = $fs->create_file_from_string($fileinfo, $newcontents);
                if ($pathhash) {
                    return $newfile->get_pathnamehash();
                }
            }
        } else {
            debugging('Error baking badge image!', DEBUG_DEVELOPER);
            return;
        }
    }

        if ($pathhash) {
        $file = $fs->get_file($user_context->id, 'badges', 'userbadge', $badge->id, '/', $hash . '.png');
        return $file->get_pathnamehash();
    }

    $fileurl = moodle_url::make_pluginfile_url($user_context->id, 'badges', 'userbadge', $badge->id, '/', $hash, true);
    return $fileurl;
}


function get_backpack_settings($userid, $refresh = false) {
    global $DB;
    require_once(dirname(dirname(__FILE__)) . '/badges/lib/backpacklib.php');

        $badgescache = cache::make('core', 'externalbadges');
    $out = $badgescache->get($userid);
    if ($out !== false && !$refresh) {
        return $out;
    }
        $record = $DB->get_record('badge_backpack', array('userid' => $userid));
    if ($record) {
        $backpack = new OpenBadgesBackpackHandler($record);
        $out = new stdClass();
        $out->backpackurl = $backpack->get_url();

        if ($collections = $DB->get_records('badge_external', array('backpackid' => $record->id))) {
            $out->totalcollections = count($collections);
            $out->totalbadges = 0;
            $out->badges = array();
            foreach ($collections as $collection) {
                $badges = $backpack->get_badges($collection->collectionid);
                if (isset($badges->badges)) {
                    $out->badges = array_merge($out->badges, $badges->badges);
                    $out->totalbadges += count($badges->badges);
                } else {
                    $out->badges = array_merge($out->badges, array());
                }
            }
        } else {
            $out->totalbadges = 0;
            $out->totalcollections = 0;
        }

        $badgescache->set($userid, $out);
        return $out;
    }

    return null;
}


function badges_download($userid) {
    global $CFG, $DB;
    $context = context_user::instance($userid);
    $records = $DB->get_records('badge_issued', array('userid' => $userid));

        $fs = get_file_storage();
    $filelist = array();
    foreach ($records as $issued) {
        $badge = new badge($issued->badgeid);
                $name =  $badge->name . ' ' . userdate($issued->dateissued, '%d %b %Y') . ' ' . hash('crc32', $badge->id);
        $name = str_replace(' ', '_', $name);
        if ($file = $fs->get_file($context->id, 'badges', 'userbadge', $issued->badgeid, '/', $issued->uniquehash . '.png')) {
            $filelist[$name . '.png'] = $file;
        }
    }

        $tempzip = tempnam($CFG->tempdir.'/', 'mybadges');
    $zipper = new zip_packer();
    if ($zipper->archive_to_pathname($filelist, $tempzip)) {
        send_temp_file($tempzip, 'badges.zip');
    } else {
        debugging("Problems with archiving the files.", DEBUG_DEVELOPER);
        die;
    }
}


function badges_check_backpack_accessibility() {
    global $CFG;
    include_once $CFG->libdir . '/filelib.php';

        $fakeassertion = new moodle_url('/badges/assertion.php', array('b' => 'abcd1234567890'));

        $curl = new curl();
    $options = array(
        'FRESH_CONNECT' => true,
        'RETURNTRANSFER' => true,
        'HEADER' => 0,
        'CONNECTTIMEOUT' => 2,
    );
    $location = BADGE_BACKPACKURL . '/baker';
    $out = $curl->get($location, array('assertion' => $fakeassertion->out(false)), $options);

    $data = json_decode($out);
    if (!empty($curl->error)) {
        return 'curl-request-timeout';
    } else {
        if (isset($data->code) && $data->code == 'http-unreachable') {
            return 'http-unreachable';
        } else {
            return 'available';
        }
    }

    return false;
}


function badges_user_has_backpack($userid) {
    global $DB;
    return $DB->record_exists('badge_backpack', array('userid' => $userid));
}


function badges_handle_course_deletion($courseid) {
    global $CFG, $DB;
    include_once $CFG->libdir . '/filelib.php';

    $systemcontext = context_system::instance();
    $coursecontext = context_course::instance($courseid);
    $fs = get_file_storage();

        $fs->move_area_files_to_new_context($coursecontext->id, $systemcontext->id, 'badges', 'badgeimage');

        $badges = $DB->get_records('badge', array('type' => BADGE_TYPE_COURSE, 'courseid' => $courseid));
    foreach ($badges as $badge) {
                $toupdate = new stdClass();
        $toupdate->id = $badge->id;
        $toupdate->type = BADGE_TYPE_SITE;
        $toupdate->courseid = null;
        $toupdate->status = BADGE_STATUS_ARCHIVED;
        $DB->update_record('badge', $toupdate);
    }
}


function badges_setup_backpack_js() {
    global $CFG, $PAGE;
    if (!empty($CFG->badges_allowexternalbackpack)) {
        $PAGE->requires->string_for_js('error:backpackproblem', 'badges');
        $PAGE->requires->js(new moodle_url(BADGE_BACKPACKURL . '/issuer.js'), true);
        $PAGE->requires->js('/badges/backpack.js', true);
    }
}
