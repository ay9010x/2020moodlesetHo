<?php


namespace core\event;

use core\event\base;
use core_competency\user_evidence;

defined('MOODLE_INTERNAL') || die();


class competency_user_evidence_deleted extends base {

    
    public static final function create_from_user_evidence(user_evidence $userevidence) {
        if (!$userevidence->get_id()) {
            throw new \coding_exception('The evidence of prior learning ID must be set.');
        }
        $event = static::create(array(
            'contextid'  => $userevidence->get_context()->id,
            'objectid' => $userevidence->get_id(),
            'relateduserid' => $userevidence->get_userid()
        ));
        $event->add_record_snapshot(user_evidence::TABLE, $userevidence->to_record());
        return $event;
    }

    
    public static function get_name() {
        return get_string('eventuserevidencedeleted', 'core_competency');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the evidence of prior learning with id '$this->objectid'.";
    }

    
    protected function init() {
        $this->data['objecttable'] = user_evidence::TABLE;
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
