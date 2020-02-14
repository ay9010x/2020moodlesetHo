<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


abstract class assessable_uploaded extends base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    protected function validate_data() {
        parent::validate_data();
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        } else if (!isset($this->other['pathnamehashes']) || !is_array($this->other['pathnamehashes'])) {
            throw new \coding_exception('The \'pathnamehashes\' value must be set in other and must be an array.');
        } else if (!isset($this->other['content']) || !is_string($this->other['content'])) {
            throw new \coding_exception('The \'content\' value must be set in other and must be a string.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
