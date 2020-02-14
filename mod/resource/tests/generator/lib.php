<?php



defined('MOODLE_INTERNAL') || die();



class mod_resource_generator extends testing_module_generator {

    
    public function create_instance($record = null, array $options = null) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/lib/resourcelib.php');
                $record = (object)(array)$record;

                if (!isset($record->display)) {
            $record->display = RESOURCELIB_DISPLAY_AUTO;
        }
        if (!isset($record->printintro)) {
            $record->printintro = 0;
        }
        if (!isset($record->showsize)) {
            $record->showsize = 0;
        }
        if (!isset($record->showtype)) {
            $record->showtype = 0;
        }

                        if (!isset($record->files)) {
            if (empty($USER->username) || $USER->username === 'guest') {
                throw new coding_exception('resource generator requires a current user');
            }
            $usercontext = context_user::instance($USER->id);

                        $record->files = file_get_unused_draft_itemid();

                        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                    'contextid' => $usercontext->id, 'itemid' => $record->files,
                    'filename' => 'resource' . ($this->instancecount+1) . '.txt', 'filepath' => '/');
            $fs = get_file_storage();
            $fs->create_file_from_string($filerecord, 'Test resource ' . ($this->instancecount+1) . ' file');
        }

                return parent::create_instance($record, (array)$options);
    }
}
