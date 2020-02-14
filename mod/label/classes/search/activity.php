<?php



namespace mod_label\search;

defined('MOODLE_INTERNAL') || die();


class activity extends \core_search\area\base_activity {

    
    public function get_doc_url(\core_search\document $doc) {
        $cminfo = $this->get_cm($this->get_module_name(), strval($doc->get('itemid')), $doc->get('courseid'));
        return new \moodle_url('/course/view.php', array('id' => $doc->get('courseid')), 'module-' . $cminfo->id);

    }

    
    public function get_context_url(\core_search\document $doc) {
        return new \moodle_url('/course/view.php', array('id' => $doc->get('courseid')));

    }

}
