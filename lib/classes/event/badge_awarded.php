<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class badge_awarded extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'badge';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventbadgeawarded', 'badges');
    }

    
    public function get_description() {
        return "The user with id '$this->relateduserid' has been awarded the badge with id '".$this->objectid."'.";
    }

    
    public function get_url() {
        return new \moodle_url('/badges/overview.php', array('id' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'badge', 'restore' => 'badge');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['badgeissuedid'] = array('db' => 'badge_issued', 'restore' => base::NOT_MAPPED);

        return $othermapped;
    }
}
