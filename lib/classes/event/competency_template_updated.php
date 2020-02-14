<?php



namespace core\event;

use core\event\base;
use core_competency\template;

defined('MOODLE_INTERNAL') || die();


class competency_template_updated extends base {

    
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
        return "The user with id '$this->userid' updated the template with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventtemplateupdated', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::template($this->objectid, $this->contextid);
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = template::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
