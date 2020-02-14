<?php



namespace mod_lightboxgallery\event;

defined('MOODLE_INTERNAL') || die();


class image_updated extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has updated the image with name '{$this->other['imagename']}' " .
            " in the lightboxgallery with the course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventimageupdated', 'mod_lightboxgallery');
    }

    
    public function get_url() {
        $params = array(
            'id' => $this->contextinstanceid,
            'image' => $this->other['imagename'],
            'tab' => $this->other['tab'],
        );
        $url = new \moodle_url('/mod/lightboxgallery/imageedit.php', $params);
        return $url;
    }

    
    protected function get_legacy_logdata() {
                $logurl = 'view.php?id='.$this->contextinstanceid;
        return array($this->courseid, 'lightboxgallery', 'editimage', $logurl,
            $this->other['tab'].' '.$this->other['imagename'], $this->contextinstanceid, $this->userid);
    }
}
