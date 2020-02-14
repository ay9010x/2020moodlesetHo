<?php



namespace logstore_legacy\event;

defined('MOODLE_INTERNAL') || die();


class unittest_executed extends \core\event\base {
    public static function get_name() {
        return 'xxx';
    }

    public function get_description() {
        return 'yyy';
    }

    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    public function get_url() {
        return new \moodle_url('/somepath/somefile.php', array('id' => $this->data['other']['sample']));
    }

    public static function get_legacy_eventname() {
        return 'test_legacy';
    }

    protected function get_legacy_eventdata() {
        return array($this->data['courseid'], $this->data['other']['sample']);
    }

    protected function get_legacy_logdata() {
        $cmid = 0;
        if ($this->contextlevel == CONTEXT_MODULE) {
            $cmid = $this->contextinstanceid;
        }
        return array($this->data['courseid'], 'core_unittest', 'view',
            'unittest.php?id=' . $this->data['other']['sample'], 'bbb', $cmid);
    }
}
