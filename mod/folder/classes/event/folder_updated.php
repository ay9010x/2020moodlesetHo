<?php



namespace mod_folder\event;

defined('MOODLE_INTERNAL') || die();


class folder_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'folder';
    }

    
    public static function get_name() {
        return get_string('eventfolderupdated', 'mod_folder');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the folder activity with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/folder/edit.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'folder', 'edit', 'edit.php?id=' . $this->contextinstanceid, $this->objectid,
            $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'folder', 'restore' => 'folder');
    }
}
