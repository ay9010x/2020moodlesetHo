<?php

namespace core\event;

defined('MOODLE_INTERNAL') || die();


class blog_association_created extends base {

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'blog_association';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventblogassociationadded', 'core_blog');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' associated the context '{$this->other['associatetype']}' with id " .
            "'{$this->other['associateid']}' to the blog entry with id '{$this->other['blogid']}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/blog/index.php', array('entryid' => $this->other['blogid']));
    }

    
    protected function get_legacy_logdata() {
        if ($this->other['associatetype'] === 'course') {
            return array (SITEID, 'blog', 'add association', 'index.php?userid=' . $this->relateduserid. '&entryid=' .
                    $this->other['blogid'], $this->other['subject'], 0, $this->relateduserid);
        }

        return array (SITEID, 'blog', 'add association', 'index.php?userid=' . $this->relateduserid. '&entryid=' .
                $this->other['blogid'], $this->other['subject'], $this->other['associateid'], $this->relateduserid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (empty($this->other['associatetype']) || ($this->other['associatetype'] !== 'course'
                && $this->other['associatetype'] !== 'coursemodule')) {
            throw new \coding_exception('The \'associatetype\' value must be set in other and be a valid type.');
        }

        if (!isset($this->other['blogid'])) {
            throw new \coding_exception('The \'blogid\' value must be set in other.');
        }

        if (!isset($this->other['associateid'])) {
            throw new \coding_exception('The \'associateid\' value must be set in other.');
        }

        if (!isset($this->other['subject'])) {
            throw new \coding_exception('The \'subject\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'blog_association', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
                $othermapped = array();
        $othermapped['blogid'] = array('db' => 'post', 'restore' => base::NOT_MAPPED);
        
        return $othermapped;
    }
}
