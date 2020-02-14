<?php



defined('MOODLE_INTERNAL') || die();


class mod_url_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->libdir.'/resourcelib.php');

                $record = (array)$record + array(
            'display' => RESOURCELIB_DISPLAY_AUTO,
            'externalurl' => 'http://moodle.org/',
        );

        return parent::create_instance($record, (array)$options);
    }
}
