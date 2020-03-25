<?php



namespace core\event;

use core\event\base;
use core_competency\competency_framework;

defined('MOODLE_INTERNAL') || die();


class competency_framework_viewed extends base {

    
    public static function create_from_framework(competency_framework $framework) {
        if (!$framework->get_id()) {
            throw new \coding_exception('The competency framework ID must be set.');
        }
        $event = static::create(array(
            'contextid' => $framework->get_contextid(),
            'objectid' => $framework->get_id()
        ));
        $event->add_record_snapshot(competency_framework::TABLE, $framework->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the competency framework with id '$this->objectid'";
    }

    
    public static function get_name() {
        return get_string('eventcompetencyframeworkviewed', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::framework($this->objectid, $this->contextid);
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = competency_framework::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
