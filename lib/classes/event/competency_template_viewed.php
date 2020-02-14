<?php


namespace core\event;

use core\event\base;
use core_competency\template;

defined('MOODLE_INTERNAL') || die();


class competency_template_viewed extends base {

    
    public static function create_from_template(template $template) {
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

    
    public static function get_name() {
        return get_string('eventtemplateviewed', 'core_competency');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the template with id '$this->objectid'.";
    }

    
    public function get_url() {
        return \core_competency\url::template($this->objectid, $this->contextid);
    }

    
    protected function init() {
        $this->data['objecttable'] = template::TABLE;
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
