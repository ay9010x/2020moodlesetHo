<?php



namespace assignsubmission_onlinetext\event;

defined('MOODLE_INTERNAL') || die();


class submission_created extends \mod_assign\event\submission_created {

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assignsubmission_onlinetext';
    }

    
    public function get_description() {
        $descriptionstring = "The user with id '$this->userid' created an online text submission with " .
            "'{$this->other['onlinetextwordcount']}' words in the assignment with course module id " .
            "'$this->contextinstanceid'";
        if (!empty($this->other['groupid'])) {
            $descriptionstring .= " for the group with id '{$this->other['groupid']}'.";
        } else {
            $descriptionstring .= ".";
        }

        return $descriptionstring;
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['onlinetextwordcount'])) {
            throw new \coding_exception('The \'onlinetextwordcount\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'assignsubmission_onlinetext', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
