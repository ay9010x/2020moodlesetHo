<?php



defined('MOODLE_INTERNAL') || die();



class mod_page_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/resourcelib.php');

        $record = (object)(array)$record;

        if (!isset($record->content)) {
            $record->content = 'Test page content';
        }
        if (!isset($record->contentformat)) {
            $record->contentformat = FORMAT_MOODLE;
        }
        if (!isset($record->display)) {
            $record->display = RESOURCELIB_DISPLAY_AUTO;
        }
        if (!isset($record->printheading)) {
            $record->printheading = 1;
        }
        if (!isset($record->printintro)) {
            $record->printintro = 0;
        }

        return parent::create_instance($record, (array)$options);
    }
}
