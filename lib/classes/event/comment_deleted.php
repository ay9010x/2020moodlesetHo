<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


abstract class comment_deleted extends base {

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'comments';
    }

    
    public static function get_name() {
        return get_string('eventcommentdeleted', 'moodle');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the comment with id '$this->objectid' from the '$this->component' " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        $context = $this->get_context();
        if ($context) {
            return $context->get_url();
        } else {
            return null;
        }
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['itemid'])) {
            throw new \coding_exception('The \'itemid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'comments', 'restore' => 'comment');
    }

    public static function get_other_mapping() {
                $othermapped = array();
        $othermapped['itemid'] = base::NOT_MAPPED;
        return $othermapped;
    }
}
