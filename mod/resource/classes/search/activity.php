<?php



namespace mod_resource\search;

defined('MOODLE_INTERNAL') || die();


class activity extends \core_search\area\base_activity {
    
    public function uses_file_indexing() {
        return true;
    }

    
    public function attach_files($document) {
        $fs = get_file_storage();

        $cm = $this->get_cm($this->get_module_name(), $document->get('itemid'), $document->get('courseid'));
        $context = \context_module::instance($cm->id);

                $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);

        $mainfile = $files ? reset($files) : null;
        if ($mainfile && $mainfile->get_sortorder() > 0) {
            $document->add_stored_file($mainfile);
        }
    }

}
