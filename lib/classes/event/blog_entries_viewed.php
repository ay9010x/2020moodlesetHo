<?php

namespace core\event;

defined('MOODLE_INTERNAL') || die();


class blog_entries_viewed extends base {

    
    private $validparams = array('entryid', 'tagid', 'userid', 'modid', 'groupid', 'courseid', 'search', 'fromstart');

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventblogentriesviewed', 'core_blog');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed blog entries.";
    }

    
    public function get_url() {
        $params = array();
        foreach ($this->validparams as $param) {
            if (!empty($this->other[$param])) {
                $params[$param] = $this->other[$param];
            }
        }
        return new \moodle_url('/blog/index.php', $params);
    }

    
    protected function get_legacy_logdata() {
        $params = array();
        foreach ($this->validparams as $param) {
            if (!empty($this->other[$param])) {
                $params[$param] = $this->other[$param];
            }
        }
        $url = new \moodle_url('index.php', $params);
        return array (SITEID, 'blog', 'view', $url->out(), 'view blog entry');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['entryid'] = array('db' => 'post', 'restore' => base::NOT_MAPPED);
        $othermapped['tagid'] = array('db' => 'tag', 'restore' => base::NOT_MAPPED);
        $othermapped['userid'] = array('db' => 'user', 'restore' => 'user');
        $othermapped['modid'] = array('db' => 'course_modules', 'restore' => 'course_module');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');
        $othermapped['courseid'] = array('db' => 'course', 'restore' => 'course');

        return $othermapped;
    }
}
