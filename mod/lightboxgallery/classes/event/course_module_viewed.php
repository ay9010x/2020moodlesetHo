<?php



namespace mod_lightboxgallery\event;

defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'lightboxgallery';
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lightboxgallery/view.php', array('l' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'forum', 'view', 'view.php?l=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

}
