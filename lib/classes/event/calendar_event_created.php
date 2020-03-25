<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class calendar_event_created extends base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'event';
    }

    
    public static function get_name() {
        return get_string('eventcalendareventcreated', 'core_calendar');
    }

    
    public function get_description() {
        $eventname = s($this->other['name']);
        return "The user with id '$this->userid' created the event '$eventname' with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/calendar/view.php', array('view' => 'day', 'time' => $this->other['timestart']),
                'event_' . $this->objectid);
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'calendar', 'add', 'event.php?action=edit&amp;id=' . $this->objectid , $this->other['name']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['repeatid'])) {
            throw new \coding_exception('The \'repeatid\' value must be set in other.');
        }
        if (!isset($this->other['name'])) {
            throw new \coding_exception('The \'name\' value must be set in other.');
        }
        if (!isset($this->other['timestart'])) {
            throw new \coding_exception('The \'timestart\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'event', 'restore' => 'event');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['repeatid'] = array('db' => 'event', 'restore' => 'event');

        return $othermapped;
    }
}
