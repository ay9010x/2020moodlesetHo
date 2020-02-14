<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class user_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the user report for the user with id '$this->relateduserid' in " .
            "the course with id '$this->courseid' with viewing mode '{$this->other['reportmode']}'.";
    }

    
    public static function get_name() {
        return get_string('eventuserreportviewed', 'mod_forum');
    }

    
    public function get_url() {

        $url = new \moodle_url('/mod/forum/user.php', array('id' => $this->relateduserid,
            'mode' => $this->other['reportmode']));

        if ($this->courseid != SITEID) {
            $url->param('course', $this->courseid);
        }

        return $url;
    }

    
    protected function get_legacy_logdata() {
                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/forum/'));

        return array($this->courseid, 'forum', 'user report', $logurl, $this->relateduserid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['reportmode'])) {
            throw new \coding_exception('The \'reportmode\' value must be set in other.');
        }

        switch ($this->contextlevel)
        {
            case CONTEXT_COURSE:
            case CONTEXT_SYSTEM:
            case CONTEXT_USER:
                                break;
            default:
                                throw new \coding_exception('Context level must be either CONTEXT_SYSTEM, CONTEXT_COURSE or CONTEXT_USER.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}

