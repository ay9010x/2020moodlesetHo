<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class assessable_uploaded extends \core\event\assessable_uploaded {

    
    public function get_description() {
        return "The user with id '$this->userid' has posted content in the forum post with id '$this->objectid' " .
            "in the discussion '{$this->other['discussionid']}' located in the forum with course module id " .
            "'$this->contextinstanceid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename   = 'forum';
        $eventdata->name         = $this->other['triggeredfrom'];
        $eventdata->cmid         = $this->contextinstanceid;
        $eventdata->itemid       = $this->objectid;
        $eventdata->courseid     = $this->courseid;
        $eventdata->userid       = $this->userid;
        $eventdata->content      = $this->other['content'];
        if ($this->other['pathnamehashes']) {
            $eventdata->pathnamehashes = $this->other['pathnamehashes'];
        }
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'assessable_content_uploaded';
    }

    
    public static function get_name() {
        return get_string('eventassessableuploaded', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $this->other['discussionid'], 'parent' => $this->objectid));
    }

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'forum_posts';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['discussionid'])) {
            throw new \coding_exception('The \'discussionid\' value must be set in other.');
        } else if (!isset($this->other['triggeredfrom'])) {
            throw new \coding_exception('The \'triggeredfrom\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum_posts', 'restore' => 'forum_post');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['discussionid'] = array('db' => 'forum_discussions', 'restore' => 'forum_discussion');

        return $othermapped;
    }
}
