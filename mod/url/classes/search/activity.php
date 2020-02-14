<?php



namespace mod_url\search;

defined('MOODLE_INTERNAL') || die();


class activity extends \core_search\area\base_activity {

    
    public function get_document($record, $options = array()) {
        $doc = parent::get_document($record, $options);
        if (!$doc) {
            return false;
        }

        $doc->set('description1', $record->externalurl);
        return $doc;
    }
}
