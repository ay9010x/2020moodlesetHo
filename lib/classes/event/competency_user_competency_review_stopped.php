<?php



namespace core\event;

use core\event\base;
use core_competency\user_competency;
defined('MOODLE_INTERNAL') || die();


class competency_user_competency_review_stopped extends base {

    
    public static function create_from_user_competency(user_competency $usercompetency) {
        if (!$usercompetency->get_id()) {
            throw new \coding_exception('The user competency ID must be set.');
        }
        $params = array(
            'contextid' => $usercompetency->get_context()->id,
            'objectid' => $usercompetency->get_id(),
            'relateduserid' => $usercompetency->get_userid()
        );

        $event = static::create($params);
        $event->add_record_snapshot(user_competency::TABLE, $usercompetency->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' stopped a review for the user competency with id '$this->objectid'";
    }

    
    public static function get_name() {
        return get_string('eventusercompetencyreviewstopped', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::user_competency($this->objectid);
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = user_competency::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

    
    protected function validate_data() {
        if (!$this->relateduserid) {
            throw new \coding_exception('The \'relateduserid\' value must be set.');
        }
    }

}
