<?php



namespace mod_lightboxgallery\event;

defined('MOODLE_INTERNAL') || die();


class gallery_comment_created extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has created a comment " .
            " in the lightboxgallery with the course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventgallerycommentcreated', 'mod_lightboxgallery');
    }

    
    public function get_url() {
        $url = new \moodle_url('/mod/lightboxgallery/view.php', array('id' => $this->contextinstanceid));
        return $url;
    }

    
    protected function get_legacy_logdata() {
                $logurl = new \moodle_url('/mod/lightboxgallery/view.php', array('id' => $this->contextinstanceid));
        return array($this->courseid, 'lightboxgallery', 'editimage',
            $logurl, $this->other['lightboxgalleryid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
