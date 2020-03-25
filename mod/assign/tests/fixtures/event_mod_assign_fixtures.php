<?php



namespace mod_assign_unittests\event;

defined('MOODLE_INTERNAL') || die();


class submission_created extends \mod_assign\event\submission_created {
}


class submission_updated extends \mod_assign\event\submission_updated {
}


class nothing_happened extends \mod_assign\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return 'Nothing happened';
    }
}
