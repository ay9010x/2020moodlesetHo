<?php



defined('MOODLE_INTERNAL') || die();


class mod_lti_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        $record  = (object) (array) $record;

        if (!isset($record->toolurl)) {
            $record->toolurl = '';
        }
        if (!isset($record->resourcekey)) {
            $record->resourcekey = '12345';
        }
        if (!isset($record->password)) {
            $record->password = 'secret';
        }
        if (!isset($record->grade)) {
            $record->grade = 100;
        }
        if (!isset($record->instructorchoicesendname)) {
            $record->instructorchoicesendname = 1;
        }
        if (!isset($record->instructorchoicesendemailaddr)) {
            $record->instructorchoicesendemailaddr = 1;
        }
        if (!isset($record->instructorchoiceacceptgrades)) {
            $record->instructorchoiceacceptgrades = 1;
        }
        return parent::create_instance($record, (array)$options);
    }
}
