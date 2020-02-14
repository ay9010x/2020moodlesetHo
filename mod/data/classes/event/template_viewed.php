<?php



namespace mod_data\event;

defined('MOODLE_INTERNAL') || die();


class template_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventtemplateviewed', 'mod_data');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the template for the data activity with course module " .
            "id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/data/templates.php', array('d' => $this->other['dataid']));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'data', 'templates view', 'templates.php?id=' . $this->contextinstanceid .
            '&amp;d=' . $this->other['dataid'], $this->other['dataid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['dataid'])) {
            throw new \coding_exception('The \'dataid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['dataid'] = array('db' => 'data', 'restore' => 'data');

        return $othermapped;
    }
}
