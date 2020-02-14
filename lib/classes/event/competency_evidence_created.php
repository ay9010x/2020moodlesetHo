<?php


namespace core\event;

use core\event\base;
use core_competency\evidence;
use core_competency\user_competency;

defined('MOODLE_INTERNAL') || die();


class competency_evidence_created extends base {

    
    public static final function create_from_evidence(evidence $evidence, user_competency $usercompetency, $recommend) {
                if (!$evidence->get_id()) {
            throw new \coding_exception('The evidence ID must be set.');
        }

                if (!$usercompetency->get_id()) {
            throw new \coding_exception('The user competency ID must be set.');
        }

                if ($evidence->get_usercompetencyid() != $usercompetency->get_id()) {
            throw new \coding_exception('The user competency linked with this evidence is invalid.');
        }

        $event = static::create([
            'contextid'  => $evidence->get_contextid(),
            'objectid' => $evidence->get_id(),
            'userid' => $evidence->get_actionuserid(),
            'relateduserid' => $usercompetency->get_userid(),
            'other' => [
                'usercompetencyid' => $usercompetency->get_id(),
                'competencyid' => $usercompetency->get_competencyid(),
                'action' => $evidence->get_action(),
                'recommend' => $recommend
            ]
        ]);

                $event->add_record_snapshot(evidence::TABLE, $evidence->to_record());

                $event->add_record_snapshot(user_competency::TABLE, $usercompetency->to_record());

        return $event;
    }

    
    public static function get_name() {
        return get_string('eventevidencecreated', 'core_competency');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created an evidence with id '$this->objectid'.";
    }

    
    public function get_url() {
        return \core_competency\url::user_competency($this->other['usercompetencyid']);
    }

    
    protected function init() {
        $this->data['objecttable'] = evidence::TABLE;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['usercompetencyid'])) {
            throw new \coding_exception('The \'usercompetencyid\' data in \'other\' must be set.');
        }

        if (!isset($this->other['competencyid'])) {
            throw new \coding_exception('The \'competencyid\' data in \'other\' must be set.');
        }

        if (!isset($this->other['action'])) {
            throw new \coding_exception('The \'action\' data in \'other\' must be set.');
        }

        if (!isset($this->other['recommend'])) {
            throw new \coding_exception('The \'recommend\' data in \'other\' must be set.');
        }
    }
}
