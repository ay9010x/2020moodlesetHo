<?php



namespace mod_assign\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');


class activity extends \core_search\area\base_activity {
    
    public function uses_file_indexing() {
        return true;
    }

    
    public function attach_files($document) {
        $fs = get_file_storage();

        $cm = $this->get_cm($this->get_module_name(), $document->get('itemid'), $document->get('courseid'));
        $context = \context_module::instance($cm->id);

        $files = $fs->get_area_files($context->id, 'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0,
                'sortorder DESC, id ASC', false);

        foreach ($files as $file) {
            $document->add_stored_file($file);
        }
    }
}
