<?php

namespace core\event;

defined('MOODLE_INTERNAL') || die();



class blog_entry_deleted extends base {

    
    protected $blogentry;

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'post';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string("evententrydeleted", "core_blog");
    }

    
    public function set_blog_entry(\blog_entry $blogentry) {
        $this->blogentry = $blogentry;
    }

    
    public function get_blog_entry() {
        if ($this->is_restored()) {
            throw new \coding_exception('Function get_blog_entry() can not be used on restored events.');
        }
        return $this->blogentry;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the blog entry with id '$this->objectid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'blog_entry_deleted';
    }

    
    protected function get_legacy_eventdata() {
        return $this->blogentry;
    }

    
    protected function get_legacy_logdata() {
        return array (SITEID, 'blog', 'delete', 'index.php?userid=' . $this->relateduserid, 'deleted blog entry with entry id# '.
                $this->objectid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'post', 'restore' => base::NOT_MAPPED);
    }
}
