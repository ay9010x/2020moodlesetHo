<?php


namespace report_questioninstances\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_questioninstances');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the question instances report.";
    }

    
    protected function get_legacy_logdata() {
        $requestedqtype = $this->other['requestedqtype'];
        return array(SITEID, "admin", "report questioninstances", "report/questioninstances/index.php?qtype=$requestedqtype", $requestedqtype);
    }

    
    public function get_url() {
        return new \moodle_url('/report/questioninstances/index.php', array('qtype' => $this->other['requestedqtype']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['requestedqtype'])) {
            throw new \coding_exception('The \'requestedqtype\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}

