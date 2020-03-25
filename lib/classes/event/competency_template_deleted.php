<?php



namespace core\event;

use core\event\base;
use core_competency\template;

defined('MOODLE_INTERNAL') || die();


class competency_template_deleted extends base {

    
    public static final function create_from_template(template $template) {
        if (!$template->get_id()) {
            throw new \coding_exception('The template ID must be set.');
        }
        $event = static::create(array(
            'contextid'  => $template->get_contextid(),
            'objectid' => $template->get_id()
        ));
        $event->add_record_snapshot(template::TABLE, $template->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the template with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventtemplatedeleted', 'core_competency');
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = template::TABLE;;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
