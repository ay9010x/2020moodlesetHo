<?php



namespace core\event;

use core\event\base;
use core_competency\competency;

defined('MOODLE_INTERNAL') || die();


class competency_viewed extends base {

    
    public static function create_from_competency(competency $competency) {
        if (!$competency->get_id()) {
            throw new \coding_exception('The competency ID must be set.');
        }
        $event = static::create(array(
            'contextid' => $competency->get_context()->id,
            'objectid' => $competency->get_id()
        ));
        $event->add_record_snapshot(competency::TABLE, $competency->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the competency with id '$this->objectid'";
    }

    
    public static function get_name() {
        return get_string('eventcompetencyviewed', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::competency($this->objectid, $this->contextid);
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = competency::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
