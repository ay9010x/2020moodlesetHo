<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


abstract class comments_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventcommentsviewed', 'moodle');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the comments for '$this->component' with instance id '$this->objectid'.";
    }

    
    public function get_url() {
        $context = $this->get_context();
        if ($context) {
            return $context->get_url();
        } else {
            return null;
        }
    }
}
