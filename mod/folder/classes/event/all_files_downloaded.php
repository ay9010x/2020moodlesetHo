<?php



namespace mod_folder\event;

defined('MOODLE_INTERNAL') || die();


class all_files_downloaded extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'folder';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' downloaded a zip archive containing all the files from the folder activity with " .
        "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventallfilesdownloaded', 'folder');
    }

    
    public function get_url() {
        return new \moodle_url("/mod/folder/view.php", array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (empty($this->objectid) || empty($this->objecttable)) {
            throw new \coding_exception('The course_module_viewed event must define objectid and object table.');
        }
                if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
