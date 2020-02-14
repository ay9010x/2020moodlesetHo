<?php



namespace enrol_lti;

defined('MOODLE_INTERNAL') || die();


class helper {
    
    const MEMBER_SYNC_ENROL_AND_UNENROL = 1;

    
    const MEMBER_SYNC_ENROL_NEW = 2;

    
    const MEMBER_SYNC_UNENROL_MISSING = 3;

    
    const ENROLMENT_SUCCESSFUL = true;

    
    const ENROLMENT_MAX_ENROLLED = 'maxenrolledreached';

    
    const ENROLMENT_NOT_STARTED = 'enrolmentnotstarted';

    
    const ENROLMENT_FINISHED = 'enrolmentfinished';

    
    const PROFILE_IMAGE_UPDATE_SUCCESSFUL = true;

    
    const PROFILE_IMAGE_UPDATE_FAILED = 'profileimagefailed';

    
    public static function create_username($consumerkey, $ltiuserid) {
        if (!empty($ltiuserid) && !empty($consumerkey)) {
            $userkey = $consumerkey . ':' . $ltiuserid;
        } else {
            $userkey = false;
        }

        return 'enrol_lti' . sha1($consumerkey . '::' . $userkey);
    }

    
    public static function assign_user_tool_data($tool, $user) {
        global $CFG;

        $user->city = (!empty($tool->city)) ? $tool->city : "";
        $user->country = (!empty($tool->country)) ? $tool->country : "";
        $user->institution = (!empty($tool->institution)) ? $tool->institution : "";
        $user->timezone = (!empty($tool->timezone)) ? $tool->timezone : "";
        if (isset($tool->maildisplay)) {
            $user->maildisplay = $tool->maildisplay;
        } else if (isset($CFG->defaultpreference_maildisplay)) {
            $user->maildisplay = $CFG->defaultpreference_maildisplay;
        } else {
            $user->maildisplay = 2;
        }
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;
        $user->lang = $tool->lang;

        return $user;
    }

    
    public static function user_match($newuser, $olduser) {
        if ($newuser->firstname != $olduser->firstname) {
            return false;
        }
        if ($newuser->lastname != $olduser->lastname) {
            return false;
        }
        if ($newuser->email != $olduser->email) {
            return false;
        }
        if ($newuser->city != $olduser->city) {
            return false;
        }
        if ($newuser->country != $olduser->country) {
            return false;
        }
        if ($newuser->institution != $olduser->institution) {
            return false;
        }
        if ($newuser->timezone != $olduser->timezone) {
            return false;
        }
        if ($newuser->maildisplay != $olduser->maildisplay) {
            return false;
        }
        if ($newuser->mnethostid != $olduser->mnethostid) {
            return false;
        }
        if ($newuser->confirmed != $olduser->confirmed) {
            return false;
        }
        if ($newuser->lang != $olduser->lang) {
            return false;
        }

        return true;
    }

    
    public static function update_user_profile_image($userid, $url) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->libdir . '/gdlib.php');

        $fs = get_file_storage();

        $context = \context_user::instance($userid, MUST_EXIST);
        $fs->delete_area_files($context->id, 'user', 'newicon');

        $filerecord = array(
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'newicon',
            'itemid' => 0,
            'filepath' => '/'
        );

        $urlparams = array(
            'calctimeout' => false,
            'timeout' => 5,
            'skipcertverify' => true,
            'connecttimeout' => 5
        );

        try {
            $fs->create_file_from_url($filerecord, $url, $urlparams);
        } catch (\file_exception $e) {
            return get_string($e->errorcode, $e->module, $e->a);
        }

        $iconfile = $fs->get_area_files($context->id, 'user', 'newicon', false, 'itemid', false);

                $iconfile = reset($iconfile);

                if (!$iconfile = $iconfile->copy_content_to_temp()) {
            $fs->delete_area_files($context->id, 'user', 'newicon');
            return self::PROFILE_IMAGE_UPDATE_FAILED;
        }

                $newpicture = (int) process_new_icon($context, 'user', 'icon', 0, $iconfile);
                @unlink($iconfile);
                $fs->delete_area_files($context->id, 'user', 'newicon');
                $DB->set_field('user', 'picture', $newpicture, array('id' => $userid));
        return self::PROFILE_IMAGE_UPDATE_SUCCESSFUL;
    }

    
    public static function enrol_user($tool, $userid) {
        global $DB;

                if (!$DB->record_exists('user_enrolments', array('enrolid' => $tool->enrolid, 'userid' => $userid))) {
                        if ($tool->maxenrolled) {
                if ($DB->count_records('user_enrolments', array('enrolid' => $tool->enrolid)) >= $tool->maxenrolled) {
                    return self::ENROLMENT_MAX_ENROLLED;
                }
            }
                        if ($tool->enrolstartdate && time() < $tool->enrolstartdate) {
                return self::ENROLMENT_NOT_STARTED;
            }
                        if ($tool->enrolenddate && time() > $tool->enrolenddate) {
                return self::ENROLMENT_FINISHED;
            }

            $timeend = 0;
            if ($tool->enrolperiod) {
                $timeend = time() + $tool->enrolperiod;
            }

                        $instance = new \stdClass();
            $instance->id = $tool->enrolid;
            $instance->courseid = $tool->courseid;
            $instance->enrol = 'lti';
            $instance->status = $tool->status;
            $ltienrol = enrol_get_plugin('lti');

                        $timestart = intval(substr(time(), 0, 8) . '00') - 1;
            $ltienrol->enrol_user($instance, $userid, null, $timestart, $timeend);
        }

        return self::ENROLMENT_SUCCESSFUL;
    }

    
    public static function get_lti_tool($toolid) {
        global $DB;

        $sql = "SELECT elt.*, e.name, e.courseid, e.status, e.enrolstartdate, e.enrolenddate, e.enrolperiod
                  FROM {enrol_lti_tools} elt
                  JOIN {enrol} e
                    ON elt.enrolid = e.id
                 WHERE elt.id = :tid";

        return $DB->get_record_sql($sql, array('tid' => $toolid), MUST_EXIST);
    }

    
    public static function get_lti_tools($params = array(), $limitfrom = 0, $limitnum = 0) {
        global $DB;

        $sql = "SELECT elt.*, e.name, e.courseid, e.status, e.enrolstartdate, e.enrolenddate, e.enrolperiod
                  FROM {enrol_lti_tools} elt
                  JOIN {enrol} e
                    ON elt.enrolid = e.id";
        if ($params) {
            $where = "WHERE";
            foreach ($params as $colname => $value) {
                $sql .= " $where $colname = :$colname";
                $where = "AND";
            }
        }
        $sql .= " ORDER BY elt.timecreated";

        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    
    public static function count_lti_tools($params = array()) {
        global $DB;

        $sql = "SELECT COUNT(*)
                  FROM {enrol_lti_tools} elt
                  JOIN {enrol} e
                    ON elt.enrolid = e.id";
        if ($params) {
            $where = "WHERE";
            foreach ($params as $colname => $value) {
                $sql .= " $where $colname = :$colname";
                $where = "AND";
            }
        }

        return $DB->count_records_sql($sql, $params);
    }

    
    public static function create_service_body($source, $grade) {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <imsx_POXEnvelopeRequest xmlns="http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
              <imsx_POXHeader>
                <imsx_POXRequestHeaderInfo>
                  <imsx_version>V1.0</imsx_version>
                  <imsx_messageIdentifier>' . (time()) . '</imsx_messageIdentifier>
                </imsx_POXRequestHeaderInfo>
              </imsx_POXHeader>
              <imsx_POXBody>
                <replaceResultRequest>
                  <resultRecord>
                    <sourcedGUID>
                      <sourcedId>' . $source . '</sourcedId>
                    </sourcedGUID>
                    <result>
                      <resultScore>
                        <language>en-us</language>
                        <textString>' . $grade . '</textString>
                      </resultScore>
                    </result>
                  </resultRecord>
                </replaceResultRequest>
              </imsx_POXBody>
            </imsx_POXEnvelopeRequest>';
    }
}
