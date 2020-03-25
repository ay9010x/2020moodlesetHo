<?php



namespace mod_lightboxgallery\event;

defined('MOODLE_INTERNAL') || die();


class gallery_searched extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        $searchterm = s($this->other['searchterm']);
        return "The user with id '$this->userid' has searched the lightboxgallery with id '{$this->other['lightboxgalleryid']}'".
            " for lightboxgallery images containing \"{$searchterm}\".";
    }

    
    public static function get_name() {
        return get_string('eventgallerysearched', 'mod_lightboxgallery');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lightboxgallery/search.php',
            array('id' => $this->courseid, 'gallery' => $this->other['lightboxgalleryid'], 'search' => $this->other['searchterm']));
    }

    
    protected function get_legacy_logdata() {
                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/lightboxgallery/'));

        return array($this->courseid, 'lightboxgallery', 'search', $logurl, $this->other['searchterm']);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['searchterm'])) {
            throw new \coding_exception('The \'searchterm\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

}

