<?php



namespace core\event;

use core\event\base;
use core_competency\competency_framework;

defined('MOODLE_INTERNAL') || die();


class competency_framework_deleted extends base {

    
    public static final function create_from_framework(competency_framework $framework) {
        if (!$framework->get_id()) {
            throw new \coding_exception('The competency framework ID must be set.');
        }
        $event = static::create(array(
            'contextid'  => $framework->get_contextid(),
            'objectid' => $framework->get_id()
        ));
        $event->add_record_snapshot(competency_framework::TABLE, $framework->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the competency framework with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventcompetencyframeworkdeleted', 'core_competency');
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = competency_framework::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
