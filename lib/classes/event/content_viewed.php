<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();

debugging('core\event\content_viewed has been deprecated. Please extend base event or other relevant abstract class.',
        DEBUG_DEVELOPER);


abstract class content_viewed extends base {

    
    protected $legacylogdata = null;

    
    protected function init() {
        global $PAGE;

        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = $PAGE->context;
    }

    
    public function set_page_detail() {
        global $PAGE;
        if (!isset($this->data['other'])) {
            $this->data['other'] = array();
        }
        $this->data['other'] = array_merge(array('url'     => $PAGE->url->out_as_local_url(false),
                                             'heading'     => $PAGE->heading,
                                             'title'       => $PAGE->title), $this->data['other']);
    }

    
    public static function get_name() {
        return get_string('eventcontentviewed', 'moodle');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed content.";
    }

    
    public function set_legacy_logdata(array $legacydata) {
        $this->legacylogdata = $legacydata;
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (empty($this->other['content'])) {
            throw new \coding_exception('The \'content\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}

