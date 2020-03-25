<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


abstract class grade_report_viewed extends base {

    
    protected $reporttype;

    
    protected function init() {
        $reporttype = explode('\\', $this->eventname);
        $shorttype = explode('_', $reporttype[1]);
        $this->reporttype = $shorttype[1];

        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventgradeviewed', 'grades');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the $this->reporttype report in the gradebook.";
    }

    
    public function get_url() {
        $url = '/grade/report/' . $this->reporttype . '/index.php';
        return new \moodle_url($url, array('id' => $this->courseid));
    }

    
    protected function validate_data() {
        parent::validate_data();
    }
}
